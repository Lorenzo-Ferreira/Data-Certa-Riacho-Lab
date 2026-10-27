</main>

<div class="modal-overlay" id="oferta-modal" onclick="if(event.target===this) fecharDetalheOferta()">
    <div class="modal-card">
        <button type="button" class="modal-close" onclick="fecharDetalheOferta()" aria-label="Fechar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div id="om-body" class="modal-body">
            <div class="modal-loading">Carregando…</div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <div class="footer-brand">
                <span class="logo-mark footer-logo-mark"><?= logoBasketSvg(20, 'ft') ?></span>
                data<em>certa</em>
            </div>
            <div class="footer-note" style="margin-top:4px">Menos desperdício, mais economia.</div>
        </div>
        <div class="footer-links">
            <a href="index.php">Ofertas</a>
            <a href="cadastro_estabelecimento.php">Para empresas</a>
            <a href="cadastro.php">Criar conta</a>
            <a href="lgpd.php">Privacidade e LGPD</a>
        </div>
        <div class="footer-note">© 2026 DataCerta</div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
