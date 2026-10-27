<?php
if (!isset($titulo)) $titulo = 'DataCerta';

$qtd_carrinho = 0;
if (estaLogado() && eTipo('cliente')) {
    $s = $pdo->prepare("SELECT COALESCE(SUM(quantidade),0) FROM carrinho WHERE cliente_id = ?");
    $s->execute([$_SESSION['usuario_id']]);
    $qtd_carrinho = (int)$s->fetchColumn();
}

$isEstab   = estaLogado() && eTipo('estabelecimento');
$isCliente = estaLogado() && eTipo('cliente');
$ini       = estaLogado() ? mb_strtoupper(mb_substr($_SESSION['usuario_nome'], 0, 1)) : '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($titulo) ?> — DataCerta</title>
    <link rel="icon" type="image/svg+xml" href="<?= faviconHref() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script>
        (function(){
            var t = localStorage.getItem('dc-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <div class="logo-mark"><?= logoBasketSvg(26, 'hd') ?></div>
            <span class="logo-text">data<em>certa</em></span>
        </a>

        <nav class="header-nav">
            <?php if ($isEstab): ?>
                <a href="dashboard.php"    class="nav-link <?= $currentPage==='dashboard.php'    ?'active':'' ?>">Dashboard</a>
                <a href="produtos.php"     class="nav-link <?= $currentPage==='produtos.php'     ?'active':'' ?>">Produtos</a>
                <a href="vendas.php"       class="nav-link <?= $currentPage==='vendas.php'       ?'active':'' ?>">Vendas</a>
                <a href="avaliacoes.php"   class="nav-link <?= $currentPage==='avaliacoes.php'   ?'active':'' ?>">Avaliações</a>
                <a href="configuracoes.php" class="nav-link <?= $currentPage==='configuracoes.php'?'active':'' ?>">Configurações</a>
            <?php elseif ($isCliente): ?>
                <a href="index.php"        class="nav-link <?= $currentPage==='index.php'        ?'active':'' ?>">Ofertas</a>
                <a href="pedidos.php"      class="nav-link <?= $currentPage==='pedidos.php'      ?'active':'' ?>">Meus Pedidos</a>
            <?php else: ?>
                <a href="index.php"        class="nav-link <?= $currentPage==='index.php'        ?'active':'' ?>">Ver Ofertas</a>
            <?php endif; ?>
        </nav>

        <div class="nav-spacer"></div>

        <div class="header-actions">
            <button class="btn-icon" onclick="toggleTheme()" title="Alternar tema" aria-label="Alternar tema">
                <span data-theme-icon>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </span>
            </button>

            <?php if ($isCliente): ?>
            <a href="carrinho.php" class="cart-wrap btn-icon" title="Carrinho">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <span class="cart-dot" id="cart-dot" <?= $qtd_carrinho > 0 ? '' : 'style="display:none"' ?>><?= $qtd_carrinho ?></span>
            </a>
            <?php endif; ?>

            <?php if (estaLogado()): ?>
            <div class="user-chip" title="<?= sanitize($_SESSION['usuario_nome']) ?>">
                <div class="user-avatar"><?= $ini ?></div>
                <span class="user-name"><?= sanitize(explode(' ', $_SESSION['usuario_nome'])[0]) ?></span>
            </div>
            <a href="logout.php" class="btn-nav btn-nav-ghost btn-sm">Sair</a>
            <?php else: ?>
            <a href="login.php"   class="btn-nav btn-nav-ghost">Entrar</a>
            <a href="cadastro.php" class="btn-nav btn-nav-brand">Criar conta</a>
            <?php endif; ?>

            <button class="hamburger" onclick="openMenu()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu">
    <div class="mobile-menu-header">
        <div class="logo">
            <div class="logo-mark"><?= logoBasketSvg(26, 'hm') ?></div>
            <span class="logo-text">data<em>certa</em></span>
        </div>
        <button class="btn-icon" onclick="closeMenu()" style="background:var(--surface-2);border-color:var(--border);color:var(--txt-2)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <?php if ($isEstab): ?>
        <a href="dashboard.php"     class="mobile-nav-link" onclick="closeMenu()">Dashboard</a>
        <a href="produtos.php"      class="mobile-nav-link" onclick="closeMenu()">Produtos</a>
        <a href="vendas.php"        class="mobile-nav-link" onclick="closeMenu()">Vendas</a>
        <a href="avaliacoes.php"    class="mobile-nav-link" onclick="closeMenu()">Avaliações</a>
        <a href="configuracoes.php" class="mobile-nav-link" onclick="closeMenu()">Configurações</a>
    <?php elseif ($isCliente): ?>
        <a href="index.php"    class="mobile-nav-link" onclick="closeMenu()">Ofertas</a>
        <a href="carrinho.php" class="mobile-nav-link" onclick="closeMenu()">Carrinho (<?= $qtd_carrinho ?>)</a>
        <a href="pedidos.php"  class="mobile-nav-link" onclick="closeMenu()">Meus Pedidos</a>
    <?php else: ?>
        <a href="index.php"                  class="mobile-nav-link" onclick="closeMenu()">Ver Ofertas</a>
        <a href="login.php"                  class="mobile-nav-link" onclick="closeMenu()">Entrar</a>
        <a href="cadastro.php"               class="mobile-nav-link" onclick="closeMenu()">Criar conta</a>
        <a href="cadastro_estabelecimento.php" class="mobile-nav-link" onclick="closeMenu()">Sou estabelecimento</a>
    <?php endif; ?>

    <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--border)">
        <button onclick="toggleTheme();closeMenu()" class="mobile-nav-link" style="width:100%;background:none;border:none;cursor:pointer;text-align:left">
            Alternar tema
        </button>
        <?php if (estaLogado()): ?>
        <a href="logout.php" class="mobile-nav-link" style="color:var(--brand)">Sair da conta</a>
        <?php endif; ?>
    </div>
</div>

<main>
