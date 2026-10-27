/* DataCerta — main.js */

/* ── Dark mode (roda antes do DOMContentLoaded p/ evitar flash) ── */
(function () {
  const saved = localStorage.getItem('dc-theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
  const cur  = document.documentElement.getAttribute('data-theme');
  const next = cur === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('dc-theme', next);
  syncThemeBtn();
}

function syncThemeBtn() {
  const dark = document.documentElement.getAttribute('data-theme') === 'dark';
  document.querySelectorAll('[data-theme-icon]').forEach(el => {
    el.textContent = dark ? '☀️' : '🌙';
    el.setAttribute('aria-label', dark ? 'Ativar modo claro' : 'Ativar modo escuro');
    el.setAttribute('title', dark ? 'Modo claro' : 'Modo escuro');
  });
}

/* ── Mobile menu ─────────────────────────────────────────────────── */
function openMenu() {
  const m = document.getElementById('mobile-menu');
  if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeMenu() {
  const m = document.getElementById('mobile-menu');
  if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}

/* ── Category filter ─────────────────────────────────────────────── */
function filtrarCategoria(cat, btn) {
  document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const cards = document.querySelectorAll('.product-card[data-cat]');
  let n = 0;
  cards.forEach(c => {
    const show = cat === 'todos' || c.dataset.cat === cat;
    c.style.display = show ? '' : 'none';
    if (show) n++;
  });
  const nr = document.getElementById('no-results');
  if (nr) nr.style.display = n === 0 ? 'block' : 'none';
}

/* ── Search ──────────────────────────────────────────────────────── */
function pesquisar() {
  const q = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
  const cards = document.querySelectorAll('.product-card[data-nome]');
  let n = 0;
  cards.forEach(c => {
    const match = !q || c.dataset.nome.toLowerCase().includes(q)
                      || (c.dataset.loja || '').toLowerCase().includes(q);
    c.style.display = match ? '' : 'none';
    if (match) n++;
  });
  const nr = document.getElementById('no-results');
  if (nr) nr.style.display = n === 0 ? 'block' : 'none';
}

/* ── Cart ────────────────────────────────────────────────────────── */
function adicionarCarrinho(id, btn) {
  btn.disabled = true;
  const orig = btn.textContent;
  btn.textContent = '…';

  fetch('carrinho_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `acao=adicionar&produto_id=${id}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.login) { location.href = 'login.php'; return; }
    if (d.sucesso) {
      btn.textContent = '✓ Adicionado';
      btn.classList.add('added');
      atualizarContador(d.total_itens);
      setTimeout(() => {
        btn.textContent = orig;
        btn.classList.remove('added');
        btn.disabled = false;
      }, 2200);
    } else {
      btn.textContent = d.mensagem || 'Erro';
      btn.style.background = 'var(--amber)';
      btn.style.color = '#fff';
      setTimeout(() => {
        btn.textContent = orig;
        btn.style.background = '';
        btn.style.color = '';
        btn.disabled = false;
      }, 2000);
    }
  })
  .catch(() => { btn.textContent = orig; btn.disabled = false; });
}

function atualizarContador(n) {
  document.querySelectorAll('.cart-dot').forEach(el => {
    el.textContent = n;
    el.style.display = n > 0 ? 'flex' : 'none';
  });
}

function alterarQtd(itemId, delta) {
  const inp = document.getElementById(`qty-${itemId}`);
  if (!inp) return;
  const v = parseInt(inp.value) + delta;
  if (v < 1) { removerItem(itemId); return; }
  inp.value = v;
  atualizarCarrinho(itemId, v);
}

function atualizarCarrinho(itemId, qtd) {
  fetch('carrinho_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `acao=atualizar&item_id=${itemId}&quantidade=${qtd}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.sucesso) {
      atualizarContador(d.total_itens);
      _setEl('cart-subtotal', 'R$ ' + _fmt(d.subtotal));
      _setEl('cart-total', 'R$ ' + _fmt(d.total));
    }
  });
}

function removerItem(itemId) {
  fetch('carrinho_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `acao=remover&item_id=${itemId}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.sucesso) {
      document.getElementById(`cart-item-${itemId}`)?.remove();
      atualizarContador(d.total_itens);
      _setEl('cart-subtotal', 'R$ ' + _fmt(d.subtotal));
      _setEl('cart-total', 'R$ ' + _fmt(d.total));
      if (d.total_itens === 0) location.reload();
    }
  });
}

function _setEl(id, txt) { const e = document.getElementById(id); if (e) e.textContent = txt; }
function _fmt(n) { return parseFloat(n).toFixed(2).replace('.', ','); }
function _escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s == null ? '' : String(s);
  return d.innerHTML;
}

/* ── Quantidade limitada ao estoque ──────────────────────────────── */
function clampQty(input) {
  const min = input.min !== '' ? parseInt(input.min) : 0;
  const max = input.max !== '' ? parseInt(input.max) : Infinity;
  let v = parseInt(input.value);
  if (isNaN(v)) v = min;
  if (v < min) v = min;
  if (v > max) v = max;
  input.value = v;
}

/* ── Modal de detalhe da oferta ──────────────────────────────────── */
function abrirDetalheOferta(id) {
  const modal = document.getElementById('oferta-modal');
  const body  = document.getElementById('om-body');
  if (!modal || !body) return;
  body.innerHTML = '<div class="modal-loading">Carregando…</div>';
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  fetch('produto_detalhe.php?id=' + encodeURIComponent(id))
    .then(r => r.json())
    .then(d => {
      body.innerHTML = d.sucesso
        ? renderDetalheOferta(d)
        : `<div class="modal-loading">${_escHtml(d.mensagem || 'Não foi possível carregar esta oferta.')}</div>`;
    })
    .catch(() => { body.innerHTML = '<div class="modal-loading">Erro ao carregar a oferta.</div>'; });
}

function fecharDetalheOferta() {
  const modal = document.getElementById('oferta-modal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
}

function renderDetalheOferta(d) {
  const desconto = Math.round((1 - d.preco_promocional / d.preco_original) * 100);
  const imgHtml = d.imagem
    ? `<img src="uploads/${encodeURI(d.imagem)}" alt="${_escHtml(d.nome)}">`
    : `<div class="modal-img-placeholder">${_escHtml((d.categoria || '').toUpperCase())}</div>`;

  const maxQtd = Math.max(0, d.disponivel);
  const semEstoque = maxQtd < 1;

  const acoesHtml = d.pode_comprar ? `
    <div class="modal-qty-row">
      <span>Quantidade</span>
      <div class="qty-ctrl">
        <button type="button" class="qty-btn" onclick="alterarQtdModal(-1)">−</button>
        <input type="number" id="om-qtd" class="qty-val" value="${semEstoque ? 0 : 1}"
               min="${semEstoque ? 0 : 1}" max="${maxQtd}" oninput="clampQty(this)">
        <button type="button" class="qty-btn" onclick="alterarQtdModal(1)">+</button>
      </div>
    </div>
    <div class="field-hint" style="margin-bottom:14px">
      ${semEstoque ? 'O estoque disponível já está no seu carrinho.' : maxQtd + ' unidade(s) disponível(is)'}
    </div>
    <button type="button" class="btn btn-primary btn-block" id="om-add" ${semEstoque ? 'disabled' : ''}
            onclick="adicionarCarrinhoModal(${d.id})">
      Adicionar ao carrinho
    </button>
    <div id="om-msg" class="field-hint" style="text-align:center;margin-top:8px"></div>
  ` : !d.logado ? `
    <a href="login.php" class="btn btn-primary btn-block">Entrar para comprar</a>
    <a href="cadastro.php" class="btn btn-secondary btn-block" style="margin-top:8px">Criar conta grátis</a>
  ` : `<div class="field-hint" style="text-align:center">Apenas contas de cliente podem comprar.</div>`;

  const linksHtml = d.logado
    ? `<a href="${d.maps_url}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Ver no mapa</a>
      ${d.whatsapp_url ? `<a href="${d.whatsapp_url}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">Falar no WhatsApp</a>` : ''}`
    : `<a href="login.php" class="btn btn-ghost btn-sm">Entre para ver a localização</a>`;

  return `
    <div class="modal-img-wrap">${imgHtml}<span class="discount-chip">-${desconto}%</span></div>
    <h2 class="modal-title">${_escHtml(d.nome)}</h2>
    <div class="modal-store">${_escHtml(d.estab_nome)}${d.estab_endereco ? ' · ' + _escHtml(d.estab_endereco) : ''}</div>
    <p class="modal-desc">${_escHtml(d.descricao || 'Sem descrição adicional.')}</p>

    <div class="modal-price-row">
      <span class="price-from">R$ ${_fmt(d.preco_original)}</span>
      <span class="price-now">R$ ${_fmt(d.preco_promocional)}</span>
    </div>

    <div class="modal-links-row">
      ${linksHtml}
    </div>

    <div class="modal-fulfillment">
      <span class="modal-section-label">Como você quer receber</span>
      <div class="fulfillment-options">
        <label class="fulfillment-opt is-active">
          <input type="radio" name="entrega" value="retirada" checked>
          Retirada presencial
        </label>
        <label class="fulfillment-opt is-disabled" title="Ainda não disponível">
          <input type="radio" name="entrega" value="delivery" disabled>
          Delivery <span class="soon-badge">Em breve</span>
        </label>
      </div>
    </div>

    ${acoesHtml}
  `;
}

function alterarQtdModal(delta) {
  const inp = document.getElementById('om-qtd');
  if (!inp) return;
  inp.value = (parseInt(inp.value) || 0) + delta;
  clampQty(inp);
}

function adicionarCarrinhoModal(id) {
  const inp = document.getElementById('om-qtd');
  const btn = document.getElementById('om-add');
  const msg = document.getElementById('om-msg');
  const qtd = Math.max(1, parseInt(inp && inp.value) || 1);
  if (btn) btn.disabled = true;

  fetch('carrinho_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `acao=adicionar&produto_id=${id}&quantidade=${qtd}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.sucesso) {
      atualizarContador(d.total_itens);
      if (msg) { msg.textContent = 'Adicionado ao carrinho!'; msg.style.color = 'var(--green)'; }
      setTimeout(fecharDetalheOferta, 900);
    } else {
      if (msg) { msg.textContent = d.mensagem || 'Erro ao adicionar.'; msg.style.color = 'var(--brand)'; }
      if (btn) btn.disabled = false;
    }
  })
  .catch(() => { if (btn) btn.disabled = false; });
}

/* ── Login type toggle ───────────────────────────────────────────── */
function setTipo(tipo) {
  document.querySelectorAll('.type-tab').forEach(b => b.classList.remove('active'));
  document.querySelector(`.type-tab[data-tipo="${tipo}"]`)?.classList.add('active');
  const inp = document.getElementById('tipo_usuario');
  if (inp) inp.value = tipo;
}

/* ── Image preview ───────────────────────────────────────────────── */
function previewImagem(input) {
  const pre = document.getElementById('img-preview');
  if (!pre || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    pre.src = e.target.result;
    pre.style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}

/* ── Star rating ─────────────────────────────────────────────────── */
const ratingLabels = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente!'];
function setRating(n) {
  const inp = document.getElementById('nota_val');
  if (inp) inp.value = n;
  document.querySelectorAll('.star-input-btn').forEach((s, i) => {
    s.textContent = i < n ? '★' : '☆';
    s.classList.toggle('active', i < n);
  });
  const lbl = document.getElementById('rating-label');
  if (lbl) lbl.textContent = ratingLabels[n] || '';
}

/* ── PIX copy ────────────────────────────────────────────────────── */
function copiarPix(btn) {
  const chave = btn.dataset.pix;
  if (!chave) return;
  const ok = () => {
    btn.textContent = '✓ Copiado!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'Copiar'; btn.classList.remove('copied'); }, 2200);
  };
  if (navigator.clipboard) {
    navigator.clipboard.writeText(chave).then(ok).catch(() => _pixFallback(chave, btn));
  } else {
    _pixFallback(chave, btn);
  }
}
function _pixFallback(chave, btn) {
  const ta = document.createElement('textarea');
  ta.value = chave;
  ta.style.position = 'fixed'; ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  try { document.execCommand('copy'); btn.textContent = '✓ Copiado!'; btn.classList.add('copied'); setTimeout(() => { btn.textContent = 'Copiar'; btn.classList.remove('copied'); }, 2200); }
  catch(e) { btn.textContent = 'Erro'; setTimeout(() => { btn.textContent = 'Copiar'; }, 1500); }
  document.body.removeChild(ta);
}

/* ── Confirm delete ──────────────────────────────────────────────── */
function confirmarExclusao(msg, url) {
  if (confirm(msg || 'Confirmar exclusão?')) location.href = url;
}

/* ── Auto-dismiss alerts ─────────────────────────────────────────── */
function autoDismissAlerts() {
  setTimeout(() => {
    document.querySelectorAll('.alert-auto').forEach(el => {
      el.style.transition = 'opacity .5s, transform .5s';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-8px)';
      setTimeout(() => el.remove(), 500);
    });
  }, 4500);
}

/* ── DOMContentLoaded ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  syncThemeBtn();
  autoDismissAlerts();

  // Close mobile menu on outside click
  document.getElementById('mobile-menu')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeMenu();
  });

  // Fechar modal de oferta com Esc
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fecharDetalheOferta();
  });
});
