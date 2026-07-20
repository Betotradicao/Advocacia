<?php
/**
 * Núcleo do site — leitura/gravação de conteúdo, sessão do admin e uploads.
 */

define('RAIZ', dirname(__DIR__));
define('ARQ_CONTEUDO', RAIZ . '/data/content.json');
define('ARQ_USUARIOS', RAIZ . '/data/users.php');
define('DIR_UPLOAD', RAIZ . '/assets/uploads');

/* ---------------------------------------------------------------
   Conteúdo
   --------------------------------------------------------------- */

function conteudo(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $bruto = @file_get_contents(ARQ_CONTEUDO);
    if ($bruto === false) {
        http_response_code(500);
        exit('Não foi possível ler data/content.json. Verifique se o arquivo existe e tem permissão de leitura.');
    }
    $dados = json_decode($bruto, true);
    if (!is_array($dados)) {
        http_response_code(500);
        exit('data/content.json está com formato inválido: ' . json_last_error_msg());
    }
    return $cache = $dados;
}

/**
 * Grava o conteúdo de forma atômica, para nunca deixar o arquivo pela metade
 * se o servidor cair no meio da escrita.
 */
function salvar_conteudo(array $dados): bool
{
    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $temp = ARQ_CONTEUDO . '.tmp';
    if (file_put_contents($temp, $json, LOCK_EX) === false) {
        return false;
    }
    return rename($temp, ARQ_CONTEUDO);
}

/** Busca um valor aninhado: v('contato.telefone') */
function v(string $caminho, $padrao = '')
{
    $atual = conteudo();
    foreach (explode('.', $caminho) as $parte) {
        if (!is_array($atual) || !array_key_exists($parte, $atual)) {
            return $padrao;
        }
        $atual = $atual[$parte];
    }
    return $atual;
}

/** Escapa para HTML. Use SEMPRE ao imprimir conteúdo vindo do admin. */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escapa e imprime. */
function ee($texto): void
{
    echo e($texto);
}

/** Monta o link do WhatsApp com mensagem pré-escrita. */
function link_whatsapp(string $mensagem = ''): string
{
    $numero = preg_replace('/\D/', '', (string) v('contato.whatsapp'));
    $url = 'https://wa.me/' . $numero;
    if ($mensagem !== '') {
        $url .= '?text=' . rawurlencode($mensagem);
    }
    return $url;
}

/**
 * Quebra-cache: acrescenta a data de modificação do arquivo à URL, para o
 * navegador do visitante pegar a imagem nova assim que o admin trocar.
 */
function asset(string $caminho): string
{
    $absoluto = RAIZ . '/' . ltrim($caminho, '/');
    $versao = @filemtime($absoluto);
    return e($caminho) . ($versao ? '?v=' . $versao : '');
}

/**
 * URL absoluta de um arquivo do site.
 *
 * WhatsApp, Facebook e Google exigem endereço completo nas tags de
 * compartilhamento — caminho relativo é ignorado e o link sai sem imagem.
 */
function url_absoluta(string $caminho = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'www.carloseduardoferreiraadv.com.br';

    return ($https ? 'https' : 'http') . '://' . $host . '/' . ltrim($caminho, '/');
}

/* ---------------------------------------------------------------
   Sessão e autenticação
   --------------------------------------------------------------- */

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Strict',
    ]);
    session_name('adminsess');
    session_start();
}

function usuarios(): array
{
    if (!is_file(ARQ_USUARIOS)) {
        return [];
    }
    $lista = require ARQ_USUARIOS;
    return is_array($lista) ? $lista : [];
}

function logado(): bool
{
    iniciar_sessao();
    if (empty($_SESSION['usuario'])) {
        return false;
    }
    // Expira after 2h de inatividade
    if (isset($_SESSION['visto_em']) && time() - $_SESSION['visto_em'] > 7200) {
        logout();
        return false;
    }
    $_SESSION['visto_em'] = time();
    return true;
}

function exigir_login(): void
{
    if (!logado()) {
        header('Location: index.php');
        exit;
    }
}

function login(string $usuario, string $senha): bool
{
    iniciar_sessao();
    $lista = usuarios();
    $usuario = strtolower(trim($usuario));

    if (!isset($lista[$usuario])) {
        // Gasta o mesmo tempo de um hash real, para não revelar
        // pelo tempo de resposta se o usuário existe.
        password_verify($senha, '$2y$12$usuarioinexistentexxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
        return false;
    }
    if (!password_verify($senha, $lista[$usuario])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['usuario']  = $usuario;
    $_SESSION['visto_em'] = time();
    return true;
}

function logout(): void
{
    iniciar_sessao();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------------------------------------------------------------
   Proteção contra força bruta no login
   --------------------------------------------------------------- */

function arquivo_tentativas(): string
{
    return RAIZ . '/data/.tentativas.json';
}

function tentativas_excedidas(): int
{
    $reg = @json_decode((string) @file_get_contents(arquivo_tentativas()), true);
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    if (!isset($reg[$ip])) {
        return 0;
    }
    if (time() > ($reg[$ip]['ate'] ?? 0)) {
        return 0;
    }
    return $reg[$ip]['ate'] - time(); // segundos restantes de bloqueio
}

function registrar_falha(): void
{
    $arq = arquivo_tentativas();
    $reg = @json_decode((string) @file_get_contents($arq), true) ?: [];
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';

    $agora = time();
    // limpa registros vencidos
    foreach ($reg as $chave => $dados) {
        if (($dados['ate'] ?? 0) < $agora && ($dados['ultima'] ?? 0) < $agora - 3600) {
            unset($reg[$chave]);
        }
    }

    $n = ($reg[$ip]['n'] ?? 0) + 1;
    $reg[$ip] = ['n' => $n, 'ultima' => $agora, 'ate' => 0];

    // a partir da 5ª falha, bloqueia progressivamente
    if ($n >= 5) {
        $reg[$ip]['ate'] = $agora + min(900, 30 * (2 ** ($n - 5)));
    }
    @file_put_contents($arq, json_encode($reg), LOCK_EX);
}

function limpar_falhas(): void
{
    $arq = arquivo_tentativas();
    $reg = @json_decode((string) @file_get_contents($arq), true) ?: [];
    unset($reg[$_SERVER['REMOTE_ADDR'] ?? 'desconhecido']);
    @file_put_contents($arq, json_encode($reg), LOCK_EX);
}

/* ---------------------------------------------------------------
   CSRF
   --------------------------------------------------------------- */

function token_csrf(): string
{
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validar_csrf(): void
{
    iniciar_sessao();
    $enviado = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $enviado)) {
        http_response_code(419);
        exit('Sessão expirada. Volte, recarregue a página e envie de novo.');
    }
}

/* ---------------------------------------------------------------
   Upload de imagens
   --------------------------------------------------------------- */

/**
 * Valida e move um arquivo enviado. Retorna o caminho relativo
 * (ex.: assets/uploads/foto-a1b2c3.jpg) ou lança RuntimeException.
 */
function salvar_upload(array $arquivo, string $prefixo): string
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(mensagem_erro_upload((int) $arquivo['error']));
    }
    if (!is_uploaded_file($arquivo['tmp_name'])) {
        throw new RuntimeException('Envio inválido.');
    }
    if ($arquivo['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('A imagem passa de 8 MB. Reduza o tamanho e tente de novo.');
    }

    // O tipo real vem da análise do conteúdo, não da extensão nem do que o navegador diz.
    $info = @getimagesize($arquivo['tmp_name']);
    if ($info === false) {
        throw new RuntimeException('O arquivo enviado não é uma imagem válida.');
    }

    $permitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($permitidos[$info[2]])) {
        throw new RuntimeException('Formato não aceito. Use JPG, PNG ou WEBP.');
    }
    $extensao = $permitidos[$info[2]];

    if (!is_dir(DIR_UPLOAD) && !@mkdir(DIR_UPLOAD, 0755, true) && !is_dir(DIR_UPLOAD)) {
        throw new RuntimeException('Não foi possível criar a pasta assets/uploads.');
    }

    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($prefixo));
    $nome = trim($slug, '-') . '-' . bin2hex(random_bytes(4)) . '.' . $extensao;
    $destino = DIR_UPLOAD . '/' . $nome;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        throw new RuntimeException('Falha ao gravar o arquivo. Confira a permissão da pasta assets/uploads.');
    }
    @chmod($destino, 0644);

    return 'assets/uploads/' . $nome;
}

function mensagem_erro_upload(int $codigo): string
{
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'A imagem é maior que o limite do servidor. Reduza o tamanho e tente de novo.';
        case UPLOAD_ERR_PARTIAL:
            return 'O envio foi interrompido. Tente de novo.';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo foi escolhido.';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            return 'O servidor não conseguiu gravar o arquivo temporário.';
        default:
            return 'Não foi possível enviar a imagem.';
    }
}

/**
 * Apaga uma imagem antiga que estava em assets/uploads.
 * Nunca apaga nada fora dessa pasta.
 */
function apagar_upload_antigo(?string $caminho): void
{
    if (!$caminho || strpos($caminho, 'assets/uploads/') !== 0) {
        return;
    }
    $absoluto = realpath(RAIZ . '/' . $caminho);
    $pasta    = realpath(DIR_UPLOAD);
    if ($absoluto && $pasta && strpos($absoluto, $pasta) === 0 && is_file($absoluto)) {
        @unlink($absoluto);
    }
}

/** Converte texto de textarea (uma linha por item) em array. */
function linhas_para_lista(string $texto): array
{
    $linhas = preg_split('/\r\n|\r|\n/', $texto);
    $linhas = array_map('trim', $linhas);
    return array_values(array_filter($linhas, static fn($l) => $l !== ''));
}
