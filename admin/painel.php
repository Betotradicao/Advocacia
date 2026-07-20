<?php
require dirname(__DIR__) . '/includes/app.php';
exigir_login();

$c = conteudo();
$msg  = $_GET['ok']  ?? '';
$erro = $_GET['erro'] ?? '';

$icones = [
    'balanca'   => 'Balança',
    'documento' => 'Documento',
    'familia'   => 'Família',
    'casa'      => 'Casa / Imóvel',
    'escudo'    => 'Escudo',
    'predio'    => 'Prédio',
    'martelo'   => 'Martelo',
    'aperto'    => 'Aperto de mão',
];

/** Campo de imagem: mostra a atual, permite trocar. */
function campo_imagem(string $nome, string $rotulo, ?string $atual, string $ajuda = ''): void
{ ?>
  <div class="campo campo-imagem">
    <label><?php ee($rotulo); ?></label>
    <div class="imagem-linha">
      <div class="imagem-atual">
        <?php if ($atual && is_file(RAIZ . '/' . $atual)): ?>
          <img src="../<?php echo asset($atual); ?>" alt="">
        <?php else: ?>
          <span class="sem-imagem">sem imagem</span>
        <?php endif; ?>
      </div>
      <div class="imagem-controles">
        <input type="file" name="<?php ee($nome); ?>" accept="image/jpeg,image/png,image/webp">
        <?php if ($ajuda): ?><small><?php ee($ajuda); ?></small><?php endif; ?>
        <small class="dica">Deixe em branco para manter a imagem atual. JPG, PNG ou WEBP, até 8 MB.</small>
      </div>
    </div>
  </div>
<?php }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel do site — <?php ee(v('marca.nome')); ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../<?php echo asset(v('marca.favicon')); ?>">
<link rel="stylesheet" href="admin.css?v=<?php echo @filemtime(__DIR__ . '/admin.css'); ?>">
</head>
<body>

<header class="topo">
  <div class="topo-inner">
    <div class="topo-marca">
      <img src="../<?php echo asset(v('marca.logo')); ?>" alt="">
      <span>Painel do site</span>
    </div>
    <div class="topo-acoes">
      <a href="../index.php" target="_blank" rel="noopener" class="botao botao-fantasma">Ver o site</a>
      <a href="senha.php" class="botao botao-fantasma">Trocar senha</a>
      <a href="sair.php" class="botao botao-fantasma">Sair</a>
    </div>
  </div>
</header>

<?php if ($msg): ?>
  <div class="container"><div class="aviso aviso-ok"><?php ee($msg); ?></div></div>
<?php endif; ?>
<?php if ($erro): ?>
  <div class="container"><div class="aviso aviso-erro"><?php ee($erro); ?></div></div>
<?php endif; ?>

<form class="container" method="post" action="salvar.php" enctype="multipart/form-data" id="formPainel">
<input type="hidden" name="csrf" value="<?php ee(token_csrf()); ?>">

<nav class="abas" id="abas">
  <button type="button" class="aba ativa" data-alvo="t-marca">Marca e SEO</button>
  <button type="button" class="aba" data-alvo="t-hero">Início</button>
  <button type="button" class="aba" data-alvo="t-areas">Áreas de Atuação</button>
  <button type="button" class="aba" data-alvo="t-sobre">Sobre</button>
  <button type="button" class="aba" data-alvo="t-mvv">Missão e Valores</button>
  <button type="button" class="aba" data-alvo="t-contato">Contato e Mapa</button>
  <button type="button" class="aba" data-alvo="t-rodape">Rodapé</button>
</nav>

<!-- ============ MARCA E SEO ============ -->
<section class="painel ativa" id="t-marca">
  <h2>Marca</h2>
  <div class="campo">
    <label for="marca_nome">Nome do escritório</label>
    <input type="text" id="marca_nome" name="marca_nome" value="<?php ee($c['marca']['nome']); ?>" required>
    <small class="dica">Usado no texto alternativo das imagens e em buscadores.</small>
  </div>

  <?php campo_imagem('img_logo', 'Logo (usado no topo e no rodapé)', $c['marca']['logo'] ?? null, 'Formato horizontal funciona melhor. Fundo escuro combina com o cabeçalho.'); ?>
  <?php campo_imagem('img_favicon', 'Ícone da aba do navegador', $c['marca']['favicon'] ?? null, 'Imagem quadrada, de preferência o símbolo sozinho.'); ?>
  <?php campo_imagem('img_og', 'Imagem ao compartilhar o link', $c['marca']['og_imagem'] ?? null, 'É a prévia que aparece quando o link é enviado no WhatsApp. Use 1200 x 630 pixels.'); ?>

  <h2>Buscadores (Google)</h2>
  <div class="campo">
    <label for="seo_titulo">Título da página</label>
    <input type="text" id="seo_titulo" name="seo_titulo" value="<?php ee($c['seo']['titulo']); ?>" maxlength="70">
    <small class="dica">É o texto azul que aparece no Google. Até 70 caracteres.</small>
  </div>
  <div class="campo">
    <label for="seo_descricao">Descrição</label>
    <textarea id="seo_descricao" name="seo_descricao" rows="3" maxlength="180"><?php ee($c['seo']['descricao']); ?></textarea>
    <small class="dica">O parágrafo cinza abaixo do título no Google. Até 180 caracteres.</small>
  </div>
</section>

<!-- ============ HERO ============ -->
<section class="painel" id="t-hero">
  <h2>Primeira tela</h2>
  <?php campo_imagem('img_hero', 'Imagem de fundo', $c['hero']['imagem'] ?? null, 'Horizontal e de boa resolução. O site escurece a imagem automaticamente para o texto ficar legível.'); ?>

  <div class="campo">
    <label for="hero_eyebrow">Linha de cima</label>
    <input type="text" id="hero_eyebrow" name="hero_eyebrow" value="<?php ee($c['hero']['eyebrow']); ?>">
    <small class="dica">Texto pequeno em dourado acima do título.</small>
  </div>

  <fieldset class="grupo">
    <legend>Título principal</legend>
    <small class="dica">O título é montado em três partes. A do meio aparece em dourado.</small>
    <div class="tres">
      <div class="campo">
        <label for="hero_ta">Antes</label>
        <input type="text" id="hero_ta" name="hero_titulo_antes" value="<?php ee($c['hero']['titulo_antes']); ?>">
      </div>
      <div class="campo">
        <label for="hero_td">Em dourado</label>
        <input type="text" id="hero_td" name="hero_titulo_destaque" value="<?php ee($c['hero']['titulo_destaque']); ?>">
      </div>
      <div class="campo">
        <label for="hero_tp">Depois</label>
        <input type="text" id="hero_tp" name="hero_titulo_depois" value="<?php ee($c['hero']['titulo_depois']); ?>">
      </div>
    </div>
  </fieldset>

  <div class="campo">
    <label for="hero_sub">Texto de apoio</label>
    <textarea id="hero_sub" name="hero_subtitulo" rows="3"><?php ee($c['hero']['subtitulo']); ?></textarea>
  </div>
  <div class="campo">
    <label for="hero_botao">Texto do botão</label>
    <input type="text" id="hero_botao" name="hero_botao" value="<?php ee($c['hero']['botao']); ?>">
    <small class="dica">O botão abre o WhatsApp com o número cadastrado na aba Contato.</small>
  </div>
</section>

<!-- ============ ÁREAS ============ -->
<section class="painel" id="t-areas">
  <h2>Cabeçalho da seção</h2>
  <div class="campo">
    <label for="areas_eyebrow">Linha de cima</label>
    <input type="text" id="areas_eyebrow" name="areas_eyebrow" value="<?php ee($c['areas']['eyebrow']); ?>">
  </div>
  <div class="campo">
    <label for="areas_titulo">Título</label>
    <input type="text" id="areas_titulo" name="areas_titulo" value="<?php ee($c['areas']['titulo']); ?>">
  </div>
  <div class="campo">
    <label for="areas_sub">Texto de apoio</label>
    <textarea id="areas_sub" name="areas_subtitulo" rows="2"><?php ee($c['areas']['subtitulo']); ?></textarea>
  </div>

  <h2>Áreas</h2>
  <p class="dica">Cada área vira um card. Com 4 áreas eles ficam lado a lado; com 5 ou 6, em duas fileiras.</p>

  <div id="listaAreas">
    <?php foreach ($c['areas']['lista'] as $i => $area): ?>
    <div class="cartao-area">
      <div class="cartao-topo">
        <strong class="cartao-num">Área <?php echo $i + 1; ?></strong>
        <button type="button" class="botao botao-remover" data-remover>Remover área</button>
      </div>
      <div class="dois">
        <div class="campo">
          <label>Título</label>
          <input type="text" name="area_titulo[]" value="<?php ee($area['titulo']); ?>" required>
        </div>
        <div class="campo">
          <label>Ícone</label>
          <select name="area_icone[]">
            <?php foreach ($icones as $chave => $nome): ?>
            <option value="<?php ee($chave); ?>" <?php echo ($area['icone'] ?? '') === $chave ? 'selected' : ''; ?>><?php ee($nome); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="campo">
        <label>Serviços</label>
        <textarea name="area_itens[]" rows="7"><?php ee(implode("\n", $area['itens'] ?? [])); ?></textarea>
        <small class="dica">Um serviço por linha.</small>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <button type="button" class="botao botao-secundario" id="addArea">+ Adicionar área</button>

  <template id="modeloArea">
    <div class="cartao-area">
      <div class="cartao-topo">
        <strong class="cartao-num">Nova área</strong>
        <button type="button" class="botao botao-remover" data-remover>Remover área</button>
      </div>
      <div class="dois">
        <div class="campo">
          <label>Título</label>
          <input type="text" name="area_titulo[]" value="" required>
        </div>
        <div class="campo">
          <label>Ícone</label>
          <select name="area_icone[]">
            <?php foreach ($icones as $chave => $nome): ?>
            <option value="<?php ee($chave); ?>"><?php ee($nome); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="campo">
        <label>Serviços</label>
        <textarea name="area_itens[]" rows="7"></textarea>
        <small class="dica">Um serviço por linha.</small>
      </div>
    </div>
  </template>
</section>

<!-- ============ SOBRE ============ -->
<section class="painel" id="t-sobre">
  <h2>Sobre Nós</h2>
  <div class="campo">
    <label for="sobre_eyebrow">Linha de cima</label>
    <input type="text" id="sobre_eyebrow" name="sobre_eyebrow" value="<?php ee($c['sobre']['eyebrow']); ?>">
  </div>
  <div class="campo">
    <label for="sobre_titulo">Título</label>
    <input type="text" id="sobre_titulo" name="sobre_titulo" value="<?php ee($c['sobre']['titulo']); ?>">
  </div>
  <div class="campo">
    <label for="sobre_texto">Texto</label>
    <textarea id="sobre_texto" name="sobre_texto" rows="6"><?php ee($c['sobre']['texto']); ?></textarea>
  </div>

  <?php campo_imagem('img_sobre', 'Foto', $c['sobre']['foto'] ?? null, 'Retrato em pé. O site corta no formato 4:5 automaticamente.'); ?>

  <div class="dois">
    <div class="campo">
      <label for="sobre_nome">Nome na legenda da foto</label>
      <input type="text" id="sobre_nome" name="sobre_foto_nome" value="<?php ee($c['sobre']['foto_nome']); ?>">
    </div>
    <div class="campo">
      <label for="sobre_cargo">Cargo na legenda</label>
      <input type="text" id="sobre_cargo" name="sobre_foto_cargo" value="<?php ee($c['sobre']['foto_cargo']); ?>">
    </div>
  </div>
  <div class="campo">
    <label for="sobre_botao">Texto do botão</label>
    <input type="text" id="sobre_botao" name="sobre_botao" value="<?php ee($c['sobre']['botao']); ?>">
  </div>
</section>

<!-- ============ MVV ============ -->
<section class="painel" id="t-mvv">
  <h2>Missão, Visão e Valores</h2>
  <div class="dois">
    <div class="campo">
      <label for="mvv_eyebrow">Linha de cima</label>
      <input type="text" id="mvv_eyebrow" name="mvv_eyebrow" value="<?php ee($c['mvv']['eyebrow']); ?>">
    </div>
    <div class="campo">
      <label for="mvv_titulo">Título</label>
      <input type="text" id="mvv_titulo" name="mvv_titulo" value="<?php ee($c['mvv']['titulo']); ?>">
    </div>
  </div>
  <div class="campo">
    <label for="mvv_missao">Missão</label>
    <textarea id="mvv_missao" name="mvv_missao" rows="6"><?php ee($c['mvv']['missao']); ?></textarea>
  </div>
  <div class="campo">
    <label for="mvv_visao">Visão</label>
    <textarea id="mvv_visao" name="mvv_visao" rows="6"><?php ee($c['mvv']['visao']); ?></textarea>
  </div>
  <div class="campo">
    <label for="mvv_valores">Valores</label>
    <textarea id="mvv_valores" name="mvv_valores" rows="10"><?php ee(implode("\n", $c['mvv']['valores'] ?? [])); ?></textarea>
    <small class="dica">Um valor por linha.</small>
  </div>

  <h2>Nossa Essência</h2>
  <div class="campo">
    <label for="ess_eyebrow">Linha de cima</label>
    <input type="text" id="ess_eyebrow" name="essencia_eyebrow" value="<?php ee($c['essencia']['eyebrow']); ?>">
  </div>
  <div class="campo">
    <label for="ess_texto">Frase em destaque</label>
    <textarea id="ess_texto" name="essencia_texto" rows="5"><?php ee($c['essencia']['texto']); ?></textarea>
  </div>
</section>

<!-- ============ CONTATO ============ -->
<section class="painel" id="t-contato">
  <h2>Cabeçalho da seção</h2>
  <div class="dois">
    <div class="campo">
      <label for="ct_eyebrow">Linha de cima</label>
      <input type="text" id="ct_eyebrow" name="contato_eyebrow" value="<?php ee($c['contato']['eyebrow']); ?>">
    </div>
    <div class="campo">
      <label for="ct_titulo">Título</label>
      <input type="text" id="ct_titulo" name="contato_titulo" value="<?php ee($c['contato']['titulo']); ?>">
    </div>
  </div>
  <div class="campo">
    <label for="ct_sub">Texto de apoio</label>
    <textarea id="ct_sub" name="contato_subtitulo" rows="2"><?php ee($c['contato']['subtitulo']); ?></textarea>
  </div>

  <h2>Dados de contato</h2>
  <div class="dois">
    <div class="campo">
      <label for="ct_tel">Telefone que aparece no site</label>
      <input type="text" id="ct_tel" name="contato_telefone" value="<?php ee($c['contato']['telefone']); ?>">
      <small class="dica">Como você quer que apareça. Ex.: (12) 99660-6498</small>
    </div>
    <div class="campo">
      <label for="ct_wa">Número do WhatsApp</label>
      <input type="text" id="ct_wa" name="contato_whatsapp" value="<?php ee($c['contato']['whatsapp']); ?>">
      <small class="dica">Só números, com 55 na frente. Ex.: 5512996606498</small>
    </div>
  </div>
  <div class="campo">
    <label for="ct_email">E-mail</label>
    <input type="email" id="ct_email" name="contato_email" value="<?php ee($c['contato']['email']); ?>">
  </div>

  <h2>Endereço e mapa</h2>
  <div class="campo">
    <label for="ct_predio">Nome do prédio</label>
    <input type="text" id="ct_predio" name="contato_endereco_predio" value="<?php ee($c['contato']['endereco_predio']); ?>">
  </div>
  <div class="campo">
    <label for="ct_rua">Rua, número e bairro</label>
    <input type="text" id="ct_rua" name="contato_endereco_rua" value="<?php ee($c['contato']['endereco_rua']); ?>">
  </div>
  <div class="campo">
    <label for="ct_cidade">Cidade, estado e CEP</label>
    <input type="text" id="ct_cidade" name="contato_endereco_cidade" value="<?php ee($c['contato']['endereco_cidade']); ?>">
  </div>
  <div class="campo">
    <label for="ct_mapa">Endereço para o mapa</label>
    <input type="text" id="ct_mapa" name="contato_mapa_busca" value="<?php ee($c['contato']['mapa_busca']); ?>">
    <small class="dica">É o que o Google Maps vai procurar. Escreva como você digitaria na busca do Maps e confira no site depois de salvar.</small>
  </div>
</section>

<!-- ============ RODAPÉ ============ -->
<section class="painel" id="t-rodape">
  <h2>Rodapé</h2>
  <div class="campo">
    <label for="rd_desc">Frase abaixo do logo</label>
    <textarea id="rd_desc" name="rodape_descricao" rows="2"><?php ee($c['rodape']['descricao']); ?></textarea>
  </div>
  <div class="campo">
    <label for="rd_copy">Direitos autorais</label>
    <input type="text" id="rd_copy" name="rodape_copyright" value="<?php ee($c['rodape']['copyright']); ?>">
    <small class="dica">O ano é preenchido sozinho, não precisa digitar.</small>
  </div>
</section>

<div class="barra-salvar">
  <button type="submit" class="botao botao-primario">Salvar alterações</button>
  <span class="dica">As mudanças aparecem no site na hora.</span>
</div>

</form>

<script src="admin.js?v=<?php echo @filemtime(__DIR__ . '/admin.js'); ?>"></script>
</body>
</html>
