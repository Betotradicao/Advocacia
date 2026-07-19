<?php
/**
 * Instalação de primeiro acesso: cria o usuário do painel.
 * Depois de rodar, este arquivo se apaga sozinho.
 */
require __DIR__ . '/includes/app.php';

// Se já existe usuário, a instalação está encerrada.
if (usuarios()) {
    if (is_file(__FILE__)) {
        @unlink(__FILE__);
    }
    header('Location: admin/index.php');
    exit;
}

$erro = '';
$pronto = false;

/** Verifica se as pastas que o painel precisa escrever estão liberadas. */
function checar_permissoes(): array
{
    $problemas = [];
    foreach (['data' => RAIZ . '/data', 'assets/uploads' => DIR_UPLOAD] as $rotulo => $caminho) {
        if (!is_dir($caminho)) {
            @mkdir($caminho, 0755, true);
        }
        if (!is_dir($caminho)) {
            $problemas[] = "A pasta $rotulo não existe e não consegui criá-la.";
        } elseif (!is_writable($caminho)) {
            $problemas[] = "A pasta $rotulo está sem permissão de escrita. Ajuste para 755 no gerenciador de arquivos da hospedagem.";
        }
    }
    return $problemas;
}

$problemas = checar_permissoes();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$problemas) {
    validar_csrf();

    $usuario = strtolower(trim((string) ($_POST['usuario'] ?? '')));
    $senha   = (string) ($_POST['senha'] ?? '');
    $conf    = (string) ($_POST['confirma'] ?? '');

    if (!preg_match('/^[a-z0-9._-]{3,32}$/', $usuario)) {
        $erro = 'O usuário deve ter de 3 a 32 caracteres, usando apenas letras, números, ponto, hífen ou sublinhado.';
    } elseif (strlen($senha) < 10) {
        $erro = 'A senha precisa ter pelo menos 10 caracteres.';
    } elseif ($senha !== $conf) {
        $erro = 'A confirmação não confere com a senha.';
    } else {
        $lista = [$usuario => password_hash($senha, PASSWORD_DEFAULT)];
        $php = "<?php\n// Gerado pelo instalador. Não edite à mão.\nreturn " . var_export($lista, true) . ";\n";

        if (@file_put_contents(ARQ_USUARIOS, $php, LOCK_EX) === false) {
            $erro = 'Não foi possível gravar data/users.php. Confira a permissão da pasta data.';
        } else {
            @chmod(ARQ_USUARIOS, 0640);
            $pronto = true;
            @unlink(__FILE__); // o instalador não deve continuar acessível
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalação do painel</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin/admin.css">
</head>
<body class="tela-login">

<main class="login-caixa login-largo">
  <h1>Instalação do painel</h1>

  <?php if ($pronto): ?>
    <div class="aviso aviso-ok">
      Usuário criado. O instalador foi apagado automaticamente por segurança.
    </div>
    <p class="login-sub">Guarde a senha em lugar seguro — não há recuperação por e-mail.</p>
    <a class="botao botao-primario" href="admin/index.php">Entrar no painel</a>

  <?php elseif ($problemas): ?>
    <div class="aviso aviso-erro">
      <strong>Ajuste as permissões antes de continuar:</strong>
      <ul>
        <?php foreach ($problemas as $p): ?><li><?php ee($p); ?></li><?php endforeach; ?>
      </ul>
    </div>
    <p class="login-sub">No gerenciador de arquivos da hospedagem, clique com o botão direito na pasta, escolha Permissões e marque 755. Depois recarregue esta página.</p>
    <a class="botao botao-secundario" href="instalar.php">Verificar de novo</a>

  <?php else: ?>
    <p class="login-sub">Crie o usuário que vai editar o site. Isso só acontece uma vez.</p>

    <?php if ($erro): ?><div class="aviso aviso-erro"><?php ee($erro); ?></div><?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?php ee(token_csrf()); ?>">

      <label for="usuario">Usuário</label>
      <input type="text" id="usuario" name="usuario" required autofocus autocomplete="username"
             value="<?php ee($_POST['usuario'] ?? ''); ?>" pattern="[A-Za-z0-9._\-]{3,32}">

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required minlength="10" autocomplete="new-password">

      <label for="confirma">Repita a senha</label>
      <input type="password" id="confirma" name="confirma" required minlength="10" autocomplete="new-password">

      <button type="submit" class="botao botao-primario">Criar usuário</button>
    </form>
  <?php endif; ?>
</main>

</body>
</html>
