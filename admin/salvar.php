<?php
require dirname(__DIR__) . '/includes/app.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel.php');
    exit;
}
validar_csrf();

function voltar(string $chave, string $texto): void
{
    header('Location: painel.php?' . $chave . '=' . rawurlencode($texto));
    exit;
}

$c = conteudo();
$t = static fn(string $campo, string $padrao = ''): string => trim((string) ($_POST[$campo] ?? $padrao));

/* ---------- textos ---------- */

$c['marca']['nome']    = $t('marca_nome');
$c['seo']['titulo']    = $t('seo_titulo');
$c['seo']['descricao'] = $t('seo_descricao');

$c['hero']['eyebrow']          = $t('hero_eyebrow');
$c['hero']['titulo_antes']     = (string) ($_POST['hero_titulo_antes'] ?? '');
$c['hero']['titulo_destaque']  = $t('hero_titulo_destaque');
$c['hero']['titulo_depois']    = (string) ($_POST['hero_titulo_depois'] ?? '');
$c['hero']['subtitulo']        = $t('hero_subtitulo');
$c['hero']['botao']            = $t('hero_botao');

$c['areas']['eyebrow']   = $t('areas_eyebrow');
$c['areas']['titulo']    = $t('areas_titulo');
$c['areas']['subtitulo'] = $t('areas_subtitulo');

/* ---------- áreas de atuação ---------- */

$titulos = $_POST['area_titulo'] ?? [];
$iconesEnviados = $_POST['area_icone'] ?? [];
$itens   = $_POST['area_itens']  ?? [];
$iconesValidos = ['balanca', 'documento', 'familia', 'casa', 'escudo', 'predio', 'martelo', 'aperto'];

$lista = [];
foreach ($titulos as $i => $titulo) {
    $titulo = trim((string) $titulo);
    if ($titulo === '') {
        continue; // área sem nome é descartada
    }
    $icone = (string) ($iconesEnviados[$i] ?? 'balanca');
    $lista[] = [
        'icone'  => in_array($icone, $iconesValidos, true) ? $icone : 'balanca',
        'titulo' => $titulo,
        'itens'  => linhas_para_lista((string) ($itens[$i] ?? '')),
    ];
}
if (!$lista) {
    voltar('erro', 'Você precisa manter pelo menos uma área de atuação.');
}
$c['areas']['lista'] = $lista;

/* ---------- sobre ---------- */

$c['sobre']['eyebrow']    = $t('sobre_eyebrow');
$c['sobre']['titulo']     = $t('sobre_titulo');
$c['sobre']['texto']      = $t('sobre_texto');
$c['sobre']['foto_nome']  = $t('sobre_foto_nome');
$c['sobre']['foto_cargo'] = $t('sobre_foto_cargo');
$c['sobre']['foto_oab']   = $t('sobre_foto_oab');
$c['sobre']['botao']      = $t('sobre_botao');

/* ---------- missão, visão, valores, essência ---------- */

$c['mvv']['eyebrow'] = $t('mvv_eyebrow');
$c['mvv']['titulo']  = $t('mvv_titulo');
$c['mvv']['missao']  = $t('mvv_missao');
$c['mvv']['visao']   = $t('mvv_visao');
$c['mvv']['valores'] = linhas_para_lista($t('mvv_valores'));

$c['essencia']['eyebrow'] = $t('essencia_eyebrow');
$c['essencia']['texto']   = $t('essencia_texto');

/* ---------- contato ---------- */

$whatsapp = preg_replace('/\D/', '', $t('contato_whatsapp'));
if ($whatsapp !== '' && strlen($whatsapp) < 12) {
    voltar('erro', 'O número do WhatsApp parece incompleto. Use o formato 55 + DDD + número, por exemplo 5512996606498.');
}

$email = $t('contato_email');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltar('erro', 'O e-mail digitado não é válido.');
}

$c['contato']['eyebrow']          = $t('contato_eyebrow');
$c['contato']['titulo']           = $t('contato_titulo');
$c['contato']['subtitulo']        = $t('contato_subtitulo');
$c['contato']['telefone']         = $t('contato_telefone');
$c['contato']['whatsapp']         = $whatsapp;
$c['contato']['email']            = $email;
$c['contato']['endereco_predio']  = $t('contato_endereco_predio');
$c['contato']['endereco_rua']     = $t('contato_endereco_rua');
$c['contato']['endereco_cidade']  = $t('contato_endereco_cidade');
$c['contato']['mapa_busca']       = $t('contato_mapa_busca');

/* ---------- rodapé ---------- */

$c['rodape']['descricao'] = $t('rodape_descricao');
$c['rodape']['copyright'] = $t('rodape_copyright');

/* ---------- imagens ---------- */

$imagens = [
    'img_logo'    => ['marca', 'logo',    'logo'],
    'img_favicon' => ['marca', 'favicon', 'icone'],
    'img_og'      => ['marca', 'og_imagem', 'compartilhamento'],
    'img_hero'    => ['hero',  'imagem',  'capa'],
    'img_sobre'   => ['sobre', 'foto',    'foto'],
];

$trocadas = [];
foreach ($imagens as $campo => [$secao, $chave, $prefixo]) {
    if (($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        continue; // não enviou nada, mantém a atual
    }
    try {
        $novo = salvar_upload($_FILES[$campo], $prefixo);
    } catch (RuntimeException $ex) {
        voltar('erro', $ex->getMessage());
    }
    $trocadas[] = [$c[$secao][$chave] ?? null, $novo];
    $c[$secao][$chave] = $novo;
}

/* ---------- grava ---------- */

if (!salvar_conteudo($c)) {
    // desfaz as imagens recém-enviadas, já que o conteúdo não foi salvo
    foreach ($trocadas as [$antigo, $novo]) {
        apagar_upload_antigo($novo);
    }
    voltar('erro', 'Não foi possível gravar as alterações. Confira se a pasta data tem permissão de escrita (chmod 755) e se o arquivo content.json está com 644.');
}

// Só agora apaga as imagens substituídas, com o novo conteúdo já salvo.
foreach ($trocadas as [$antigo, $novo]) {
    apagar_upload_antigo($antigo);
}

voltar('ok', 'Alterações salvas. O site já está atualizado.');
