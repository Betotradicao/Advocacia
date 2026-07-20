<?php
require __DIR__ . '/includes/app.php';

/** Ícones das áreas de atuação, escolhidos pelo admin. */
function icone_area(string $nome): string
{
    $icones = [
        'balanca'   => '<path d="M12 3v18M7 21h10M12 6l-6 2 3 6a3.5 3.5 0 0 1-6 0l3-6M12 6l6 2-3 6a3.5 3.5 0 0 0 6 0l-3-6"/>',
        'documento' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'familia'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'casa'      => '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/>',
        'escudo'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'predio'    => '<path d="M3 21h18M6 21V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>',
        'martelo'   => '<path d="m14 13-8.5 8.5a2.1 2.1 0 0 1-3-3L11 10M15 5l4 4M13 7l4-4 4 4-4 4zM10 12l6-6"/>',
        'aperto'    => '<path d="M11 17 9 19a2 2 0 0 1-3-3l6-6 4 4M3 11l4-4 5 5M14 8l3-3 4 4-3 3"/>',
    ];
    return $icones[$nome] ?? $icones['balanca'];
}

$areas = v('areas.lista', []);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php ee(v('seo.titulo')); ?></title>
<meta name="description" content="<?php ee(v('seo.descricao')); ?>">
<link rel="canonical" href="<?php echo e(url_absoluta()); ?>">
<link rel="icon" href="<?php echo asset(v('marca.favicon')); ?>">

<?php // Prévia ao compartilhar no WhatsApp, Facebook, LinkedIn e Telegram
$og_img = v('marca.og_imagem') ?: v('marca.logo');
$og_ver = @filemtime(RAIZ . '/' . $og_img); ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?php ee(v('marca.nome')); ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:url" content="<?php echo e(url_absoluta()); ?>">
<meta property="og:title" content="<?php ee(v('seo.titulo')); ?>">
<meta property="og:description" content="<?php ee(v('seo.descricao')); ?>">
<meta property="og:image" content="<?php echo e(url_absoluta($og_img) . ($og_ver ? '?v=' . $og_ver : '')); ?>">
<meta property="og:image:secure_url" content="<?php echo e(url_absoluta($og_img) . ($og_ver ? '?v=' . $og_ver : '')); ?>">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?php ee(v('marca.nome')); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php ee(v('seo.titulo')); ?>">
<meta name="twitter:description" content="<?php ee(v('seo.descricao')); ?>">
<meta name="twitter:image" content="<?php echo e(url_absoluta($og_img) . ($og_ver ? '?v=' . $og_ver : '')); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime(__DIR__ . '/css/style.css'); ?>">
<style>.hero-bg{background-image:radial-gradient(ellipse at 70% 30%,rgba(201,162,39,.16),transparent 60%),url('<?php echo asset(v('hero.imagem')); ?>')}</style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="site-header" id="header">
  <div class="container header-inner">
    <a href="#hero" class="brand">
      <img src="<?php echo asset(v('marca.logo')); ?>" alt="<?php ee(v('marca.nome')); ?>">
    </a>

    <nav class="nav" id="nav">
      <a href="#hero">Home</a>
      <a href="#areas">Áreas de Atuação</a>
      <a href="#sobre">Sobre Nós</a>
      <a href="#essencia">Nossa Essência</a>
      <a href="#contato">Fale Conosco</a>
    </nav>

    <button class="nav-toggle" id="navToggle" aria-label="Abrir menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- ===== HERO ===== -->
<section class="hero" id="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <?php if (v('hero.eyebrow')): // se ficar vazio, some junto com a linha dourada ?>
    <span class="hero-eyebrow"><?php ee(v('hero.eyebrow')); ?></span>
    <?php endif; ?>
    <h1><?php ee(v('hero.titulo_antes')); ?><span class="gold"><?php ee(v('hero.titulo_destaque')); ?></span><?php ee(v('hero.titulo_depois')); ?></h1>
    <p><?php ee(v('hero.subtitulo')); ?></p>
    <div class="hero-actions">
      <a href="<?php echo e(link_whatsapp('Olá, gostaria de falar com um advogado.')); ?>" target="_blank" rel="noopener" class="btn btn-gold"><?php ee(v('hero.botao')); ?></a>
      <a href="#areas" class="btn btn-ghost">Áreas de atuação</a>
    </div>
  </div>
  <a href="#areas" class="scroll-cue" aria-label="Rolar para baixo"></a>
</section>

<!-- ===== ÁREAS DE ATUAÇÃO ===== -->
<section class="section" id="areas">
  <div class="container">
    <div class="section-head">
      <?php if (v('areas.eyebrow')): ?><span class="eyebrow"><?php ee(v('areas.eyebrow')); ?></span><?php endif; ?>
      <h2><?php ee(v('areas.titulo')); ?></h2>
      <p class="section-sub"><?php ee(v('areas.subtitulo')); ?></p>
    </div>

    <div class="areas-grid" data-colunas="<?php echo count($areas); ?>">
      <?php foreach ($areas as $area): ?>
      <article class="area-card">
        <div class="area-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><?php echo icone_area($area['icone'] ?? 'balanca'); ?></svg>
        </div>
        <h3><?php ee($area['titulo'] ?? ''); ?></h3>
        <ul>
          <?php foreach (($area['itens'] ?? []) as $item): ?>
          <li><?php ee($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo e(link_whatsapp('Olá, preciso de orientação em ' . ($area['titulo'] ?? '') . '.')); ?>" target="_blank" rel="noopener" class="area-link">Consultar advogado</a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== SOBRE NÓS ===== -->
<section class="section section-dark" id="sobre">
  <div class="container sobre-grid">
    <div class="sobre-media">
      <div class="sobre-foto">
        <img src="<?php echo asset(v('sobre.foto')); ?>" alt="<?php ee(v('sobre.foto_nome')); ?>">
      </div>
      <div class="sobre-legenda">
        <strong><?php ee(v('sobre.foto_nome')); ?></strong>
        <span><?php ee(v('sobre.foto_cargo')); ?></span>
        <?php if (v('sobre.foto_oab')): ?>
        <small><?php ee(v('sobre.foto_oab')); ?></small>
        <?php endif; ?>
      </div>
    </div>
    <div class="sobre-text">
      <?php if (v('sobre.eyebrow')): ?><span class="eyebrow"><?php ee(v('sobre.eyebrow')); ?></span><?php endif; ?>
      <h2><?php ee(v('sobre.titulo')); ?></h2>
      <p><?php ee(v('sobre.texto')); ?></p>
      <a href="#contato" class="btn btn-gold"><?php ee(v('sobre.botao')); ?></a>
    </div>
  </div>
</section>

<!-- ===== MISSÃO · VISÃO · VALORES ===== -->
<section class="section" id="mvv">
  <div class="container">
    <div class="section-head">
      <?php if (v('mvv.eyebrow')): ?><span class="eyebrow"><?php ee(v('mvv.eyebrow')); ?></span><?php endif; ?>
      <h2><?php ee(v('mvv.titulo')); ?></h2>
    </div>

    <div class="mvv-grid">
      <article class="mvv-card">
        <h3>Missão</h3>
        <p><?php ee(v('mvv.missao')); ?></p>
      </article>

      <article class="mvv-card">
        <h3>Visão</h3>
        <p><?php ee(v('mvv.visao')); ?></p>
      </article>

      <article class="mvv-card mvv-values">
        <h3>Valores</h3>
        <ul>
          <?php foreach (v('mvv.valores', []) as $valor): ?>
          <li><?php ee($valor); ?></li>
          <?php endforeach; ?>
        </ul>
      </article>
    </div>
  </div>
</section>

<!-- ===== NOSSA ESSÊNCIA ===== -->
<section class="essencia" id="essencia">
  <div class="container">
    <?php if (v('essencia.eyebrow')): ?><span class="eyebrow"><?php ee(v('essencia.eyebrow')); ?></span><?php endif; ?>
    <blockquote><?php ee(v('essencia.texto')); ?></blockquote>
  </div>
</section>

<!-- ===== CONTATO ===== -->
<section class="section section-dark" id="contato">
  <div class="container">
    <div class="section-head">
      <?php if (v('contato.eyebrow')): ?><span class="eyebrow"><?php ee(v('contato.eyebrow')); ?></span><?php endif; ?>
      <h2><?php ee(v('contato.titulo')); ?></h2>
      <p class="section-sub"><?php ee(v('contato.subtitulo')); ?></p>
    </div>

    <div class="contato-grid">
      <a class="contato-card" href="<?php echo e(link_whatsapp('Olá, gostaria de agendar um atendimento.')); ?>" target="_blank" rel="noopener">
        <div class="contato-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <h4>Telefone &amp; WhatsApp</h4>
        <p><?php ee(v('contato.telefone')); ?></p>
      </a>

      <a class="contato-card" href="mailto:<?php ee(v('contato.email')); ?>">
        <div class="contato-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
        </div>
        <h4>E-mail</h4>
        <p><?php ee(v('contato.email')); ?></p>
      </a>

      <a class="contato-card" href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo rawurlencode(v('contato.mapa_busca')); ?>" target="_blank" rel="noopener">
        <div class="contato-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <h4>Onde estamos</h4>
        <p><?php ee(v('contato.endereco_predio')); ?><br><?php ee(v('contato.endereco_rua')); ?><br><?php ee(v('contato.endereco_cidade')); ?></p>
      </a>
    </div>

    <div class="mapa">
      <iframe
        title="Localização do escritório"
        src="https://www.google.com/maps?q=<?php echo rawurlencode(v('contato.mapa_busca')); ?>&amp;output=embed"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen></iframe>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-brand">
      <img src="<?php echo asset(v('marca.logo')); ?>" alt="<?php ee(v('marca.nome')); ?>">
      <p><?php ee(v('rodape.descricao')); ?></p>
    </div>

    <div class="footer-col">
      <h5>Navegação</h5>
      <a href="#hero">Home</a>
      <a href="#areas">Áreas de Atuação</a>
      <a href="#sobre">Sobre Nós</a>
      <a href="#contato">Fale Conosco</a>
    </div>

    <div class="footer-col">
      <h5>Contato</h5>
      <a href="<?php echo e(link_whatsapp()); ?>" target="_blank" rel="noopener"><?php ee(v('contato.telefone')); ?></a>
      <a href="mailto:<?php ee(v('contato.email')); ?>"><?php ee(v('contato.email')); ?></a>
      <p><?php ee(v('contato.endereco_rua')); ?><br><?php ee(v('contato.endereco_cidade')); ?></p>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?php echo date('Y'); ?> <?php ee(v('rodape.copyright')); ?></p>
    </div>
  </div>
</footer>

<!-- ===== WHATSAPP FLUTUANTE ===== -->
<a class="wa-float" href="<?php echo e(link_whatsapp('Olá, gostaria de falar com um advogado.')); ?>" target="_blank" rel="noopener" aria-label="Falar no WhatsApp">
  <svg viewBox="0 0 32 32" fill="currentColor"><path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.19 1.6 6.02L4 29l8.13-1.55a12 12 0 0 0 3.9.65h.01C22.68 28.1 28 22.7 28 16.06 28 8.4 22.68 3 16.04 3zm0 22.1h-.01a10 10 0 0 1-3.4-.6l-.24-.09-4.83.92.93-4.7-.16-.25a10 10 0 1 1 7.71 4.72zm5.5-7.48c-.3-.15-1.78-.88-2.06-.98-.28-.1-.48-.15-.68.15s-.78.98-.95 1.18c-.18.2-.35.22-.65.08-.3-.15-1.27-.47-2.42-1.5-.9-.79-1.5-1.77-1.67-2.07-.18-.3-.02-.46.13-.61.13-.13.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.63-.93-2.24-.24-.58-.49-.5-.68-.51h-.58c-.2 0-.53.08-.8.38s-1.05 1.03-1.05 2.5 1.08 2.9 1.23 3.1c.15.2 2.12 3.24 5.13 4.54.72.31 1.28.5 1.71.63.72.23 1.37.2 1.89.12.58-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.18-1.43-.08-.13-.28-.2-.58-.35z"/></svg>
</a>

<script src="js/main.js?v=<?php echo @filemtime(__DIR__ . '/js/main.js'); ?>"></script>
</body>
</html>
