<?php
require dirname(__DIR__) . '/includes/app.php';
exigir_login();

$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();

    $atual = (string) ($_POST['atual'] ?? '');
    $nova  = (string) ($_POST['nova'] ?? '');
    $conf  = (string) ($_POST['confirma'] ?? '');
    $usuario = $_SESSION['usuario'];

    $lista = usuarios();

    if (!password_verify($atual, $lista[$usuario] ?? '')) {
        $erro = 'A senha atual está incorreta.';
    } elseif (strlen($nova) < 10) {
        $erro = 'A nova senha precisa ter pelo menos 10 caracteres.';
    } elseif ($nova !== $conf) {
        $erro = 'A confirmação não confere com a nova senha.';
    } elseif ($nova === $atual) {
        $erro = 'A nova senha precisa ser diferente da atual.';
    } else {
        $lista[$usuario] = password_hash($nova, PASSWORD_DEFAULT);

        $php = "<?php\n// Gerado pelo painel. Não edite à mão.\nreturn " . var_export($lista, true) . ";\n";
        if (@file_put_contents(ARQ_USUARIOS, $php, LOCK_EX) === false) {
            $erro = 'Não foi possível gravar a nova senha. Confira a permissão da pasta data.';
        } else {
            $msg = 'Senha alterada com sucesso.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trocar senha — Painel do site</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../<?php echo asset(v('marca.favicon')); ?>">
<link rel="stylesheet" href="admin.css?v=<?php echo @filemtime(__DIR__ . '/admin.css'); ?>">
</head>
<body>

<header class="topo">
  <div class="topo-inner">
    <div class="topo-marca">
      <img src="../<?php echo asset(v('marca.logo')); ?>" alt="">
      <span>Trocar senha</span>
    </div>
    <div class="topo-acoes">
      <a href="painel.php" class="botao botao-fantasma">Voltar ao painel</a>
      <a href="sair.php" class="botao botao-fantasma">Sair</a>
    </div>
  </div>
</header>

<main class="container container-estreito">
  <?php if ($msg): ?><div class="aviso aviso-ok"><?php ee($msg); ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="aviso aviso-erro"><?php ee($erro); ?></div><?php endif; ?>

  <section class="painel ativa">
    <h2>Trocar senha de <?php ee($_SESSION['usuario']); ?></h2>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php ee(token_csrf()); ?>">

      <div class="campo">
        <label for="atual">Senha atual</label>
        <input type="password" id="atual" name="atual" required autocomplete="current-password">
      </div>
      <div class="campo">
        <label for="nova">Nova senha</label>
        <input type="password" id="nova" name="nova" required minlength="10" autocomplete="new-password">
        <small class="dica">Mínimo de 10 caracteres. Prefira uma frase longa a uma palavra curta com símbolos.</small>
      </div>
      <div class="campo">
        <label for="confirma">Repita a nova senha</label>
        <input type="password" id="confirma" name="confirma" required minlength="10" autocomplete="new-password">
      </div>

      <button type="submit" class="botao botao-primario">Salvar nova senha</button>
    </form>
  </section>
</main>

</body>
</html>
