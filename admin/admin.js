/* Painel administrativo — abas, áreas repetíveis e aviso de saída */
(function () {
  'use strict';

  /* ---------- abas ---------- */
  var abas = document.querySelectorAll('.aba');
  var paineis = document.querySelectorAll('.painel');

  function abrir(alvo) {
    abas.forEach(function (a) { a.classList.toggle('ativa', a.dataset.alvo === alvo); });
    paineis.forEach(function (p) { p.classList.toggle('ativa', p.id === alvo); });
    try { sessionStorage.setItem('abaPainel', alvo); } catch (e) { /* modo privado */ }
  }

  abas.forEach(function (aba) {
    aba.addEventListener('click', function () { abrir(aba.dataset.alvo); });
  });

  // Reabre a última aba usada, para não perder o contexto ao salvar.
  try {
    var salva = sessionStorage.getItem('abaPainel');
    if (salva && document.getElementById(salva)) abrir(salva);
  } catch (e) { /* ignora */ }

  /* ---------- áreas de atuação ---------- */
  var lista = document.getElementById('listaAreas');
  var modelo = document.getElementById('modeloArea');
  var botaoAdd = document.getElementById('addArea');

  function renumerar() {
    lista.querySelectorAll('.cartao-area').forEach(function (cartao, i) {
      cartao.querySelector('.cartao-num').textContent = 'Área ' + (i + 1);
    });
  }

  if (botaoAdd && modelo && lista) {
    botaoAdd.addEventListener('click', function () {
      lista.appendChild(modelo.content.cloneNode(true));
      renumerar();
      var novos = lista.querySelectorAll('.cartao-area');
      novos[novos.length - 1].querySelector('input[type=text]').focus();
    });
  }

  if (lista) {
    lista.addEventListener('click', function (ev) {
      var botao = ev.target.closest('[data-remover]');
      if (!botao) return;

      if (lista.querySelectorAll('.cartao-area').length <= 1) {
        alert('É preciso manter pelo menos uma área de atuação.');
        return;
      }
      var cartao = botao.closest('.cartao-area');
      var titulo = cartao.querySelector('input[type=text]').value.trim();
      if (!confirm('Remover a área "' + (titulo || 'sem título') + '"?')) return;

      cartao.remove();
      renumerar();
      marcarSujo();
    });
  }

  /* ---------- aviso de alterações não salvas ---------- */
  var form = document.getElementById('formPainel');
  var sujo = false;

  function marcarSujo() { sujo = true; }

  if (form) {
    form.addEventListener('input', marcarSujo);
    form.addEventListener('change', marcarSujo);
    form.addEventListener('submit', function () { sujo = false; });

    window.addEventListener('beforeunload', function (ev) {
      if (!sujo) return;
      ev.preventDefault();
      ev.returnValue = '';
    });
  }
})();
