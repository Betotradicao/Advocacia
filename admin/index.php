<?php
require dirname(__DIR__) . '/includes/app.php';

if (logado()) {
    header('Location: painel.php');
    exit;
}

// Se ainda não existe usuário cadastrado, manda para a instalação.
if (!usuarios()) {
    header('Location: ../instalar.php');
    exit;
}

$erro = '';
$bloqueio = tentativas_excedidas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();

    if ($bloqueio > 0) {
        $erro = 'Muitas tentativas. Aguarde ' . ceil($bloqueio / 60) . ' minuto(s) para tentar de novo.';
    } elseif (login($_POST['usuario'] ?? '', $_POST['senha'] ?? '')) {
        limpar_falhas();
        header('Location: painel.php');
        exit;
    } else {
        registrar_falha();
        $erro = 'Usuário ou senha incorretos.';
        $bloqueio = tentativas_excedidas();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar — Painel do site</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../<?php echo asset(v('marca.favicon')); ?>">
<link rel="stylesheet" href="admin.css?v=<?php echo @filemtime(__DIR__ . '/admin.css'); ?>">
</head>
<body class="tela-login">

<main class="login-caixa">
  <img class="login-logo" src="../<?php echo asset(v('marca.logo')); ?>" alt="<?php ee(v('marca.nome')); ?>">
  <h1>Painel do site</h1>
  <p class="login-sub">Entre para editar o conteúdo.</p>

  <?php if ($erro): ?>
    <div class="aviso aviso-erro"><?php ee($erro); ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="on">
    <input type="hidden" name="csrf" value="<?php ee(token_csrf()); ?>">

    <label for="usuario">Usuário</label>
    <input type="text" id="usuario" name="usuario" required autofocus
           autocomplete="username" value="<?php ee($_POST['usuario'] ?? ''); ?>">

    <label for="senha">Senha</label>
    <input type="password" id="senha" name="senha" required autocomplete="current-password">

    <button type="submit" class="botao botao-primario" <?php echo $bloqueio > 0 ? 'disabled' : ''; ?>>Entrar</button>
  </form>

  <a class="login-voltar" href="../index.php">← Voltar para o site</a>
</main>

</body>
</html>
