<?php
require_once 'conexao.php';
$titulo = 'Ofertas Perto de Você';

$visitante = !estaLogado();

$produtos      = [];
$cats          = [];
$clienteBairro = '';
$clienteCidade = '';

$totalProdutos = (int)$pdo->query("SELECT COUNT(*) FROM produtos WHERE data_validade >= CURDATE() AND quantidade > 0 AND ativo = 1")->fetchColumn();
$totalEstabs   = (int)$pdo->query("SELECT COUNT(*) FROM estabelecimentos")->fetchColumn();
$totalPedidos  = (int)$pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();

$where  = "WHERE p.data_validade >= CURDATE() AND p.quantidade > 0 AND p.ativo = 1";
$params = [];

if (!empty($_GET['q'])) {
    $like = '%' . $_GET['q'] . '%';
    $where .= " AND (p.nome LIKE ? OR p.descricao LIKE ? OR e.nome LIKE ?)";
    $params = [$like, $like, $like];
}
if (!empty($_GET['cat']) && $_GET['cat'] !== 'todos') {
    $where .= " AND p.categoria = ?";
    $params[] = $_GET['cat'];
}

// Ordenacao por proximidade (bairro > cidade) + validade
$order = "ORDER BY";
if (eTipo('cliente')) {
    $sc = $pdo->prepare("SELECT cidade, bairro FROM clientes WHERE id = ?");
    $sc->execute([$_SESSION['usuario_id']]);
    $c = $sc->fetch();
    if ($c) {
        $clienteBairro = $c['bairro'] ?? '';
        $clienteCidade = $c['cidade'] ?? '';
        $cq = $pdo->quote($clienteCidade);
        $bq = $pdo->quote($clienteBairro);
        $order .= " (e.bairro = $bq AND e.cidade = $cq) DESC, (e.cidade = $cq) DESC,";
    }
}
$order .= " p.data_validade ASC";

$stmt = $pdo->prepare("
    SELECT p.*, e.nome AS estab_nome, e.cidade AS estab_cidade,
           e.bairro AS estab_bairro, e.rua AS estab_rua,
           e.numero AS estab_numero, e.estado AS estab_estado,
           e.cep AS estab_cep, e.id AS estab_id, e.whatsapp AS estab_whatsapp,
           DATEDIFF(p.data_validade, CURDATE()) AS dias
    FROM produtos p
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    $where $order
");
$stmt->execute($params);
$produtos = $stmt->fetchAll();

$cats = $pdo->query("
    SELECT DISTINCT categoria FROM produtos
    WHERE data_validade >= CURDATE() AND quantidade > 0 AND ativo = 1
    ORDER BY categoria
")->fetchAll(PDO::FETCH_COLUMN);

function expiryClass($dias) {
    if ($dias <= 1) return 'urgent';
    if ($dias <= 3) return 'warn';
    return 'ok';
}

include 'header.php';
?>

<section class="hero">
    <div class="hero-inner <?= $visitante ? '' : 'hero-inner--solo' ?>">
        <div class="hero-content">
            <div class="hero-badge">DataCerta, alimentando o futuro</div>
            <h1>Comida boa que<br>alimenta seu <mark>futuro</mark></h1>
            <?php if ($visitante): ?>
            <p>Padarias, mercados e hortifrutis por perto anunciam o que sobrou do dia com até 70% de desconto. Veja as ofertas de hoje e crie sua conta para comprar.</p>
            <?php else: ?>
            <p>Produtos frescos próximos da validade com até 70% de desconto. Economize e ajude a reduzir o desperdício.</p>
            <?php endif; ?>

            <form method="GET" action="index.php" class="search-wrap">
                <input type="text" id="search-input" name="q" class="search-input"
                       placeholder="Buscar produtos ou estabelecimentos..."
                       value="<?= sanitize($_GET['q'] ?? '') ?>" autocomplete="off">
                <button type="submit" class="search-btn">Buscar</button>
            </form>

            <?php if ($visitante): ?>
            <div class="hero-cta-row" style="margin-top:16px">
                <a href="cadastro.php" class="btn btn-hero-primary">Criar conta grátis</a>
                <a href="login.php" class="btn btn-hero-ghost">Já tenho conta</a>
            </div>
            <?php endif; ?>

            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-val"><?= $totalProdutos ?>+</span>
                    <span class="hero-stat-label">Produtos ativos</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-val"><?= $totalEstabs ?>+</span>
                    <span class="hero-stat-label">Estabelecimentos</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-val"><?= $totalPedidos ?>+</span>
                    <span class="hero-stat-label">Pedidos realizados</span>
                </div>
            </div>
        </div>

        <?php if ($visitante): ?>
        <div class="hero-illustration" aria-hidden="true">
            <?php include 'partials/hero_ilustracao.svg.php'; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="container section">

    <?php if ($visitante): ?>
    <div class="alert alert-info" style="margin-bottom:20px">
        Você está vendo as ofertas como visitante. Para ver a localização das lojas e comprar,
        <a href="login.php" style="font-weight:700;color:var(--brand)">entre</a> ou
        <a href="cadastro.php" style="font-weight:700;color:var(--brand)">crie sua conta grátis</a>.
    </div>
    <?php endif; ?>

    <?php if (!empty($_GET['q'])): ?>
    <div class="alert alert-info">
        Resultados para <strong>"<?= sanitize($_GET['q']) ?>"</strong> — <?= count($produtos) ?> produto(s).
        <a href="index.php" style="font-weight:700;color:var(--brand);margin-left:8px">Limpar</a>
    </div>
    <?php endif; ?>

    <?php if ($clienteBairro): ?>
    <div class="alert alert-info" style="margin-bottom:20px">
        Exibindo ofertas priorizadas para <strong><?= sanitize($clienteBairro) ?>, <?= sanitize($clienteCidade) ?></strong>.
    </div>
    <?php endif; ?>

    <div class="cat-strip">
        <button class="cat-pill <?= empty($_GET['cat']) || $_GET['cat'] === 'todos' ? 'active' : '' ?>"
                onclick="filtrarCategoria('todos', this)">
            Todos
        </button>
        <?php foreach ($cats as $cat): ?>
        <button class="cat-pill <?= ($_GET['cat'] ?? '') === $cat ? 'active' : '' ?>"
                onclick="filtrarCategoria('<?= sanitize($cat) ?>', this)">
            <?= categoriaLabel(sanitize($cat)) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px">
        <h2 class="section-title" style="margin-bottom:0">Ofertas disponíveis</h2>
        <span style="font-size:.8rem;color:var(--txt-3)"><?= count($produtos) ?> produto(s)</span>
    </div>

    <?php if (empty($produtos)): ?>
    <div class="empty">
        <h3>Nenhum produto disponivel</h3>
        <p>Não encontramos ofertas no momento. Volte em breve!</p>
        <?php if ($isEstab): ?>
        <a href="produto_form.php" class="btn btn-primary">Cadastrar produto</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="products-grid" id="products-grid">
        <?php foreach ($produtos as $p):
            $dias = (int)$p['dias'];
            $desc = (int)round((1 - $p['preco_promocional'] / $p['preco_original']) * 100);
            $ec   = expiryClass($dias);
            $maps = mapsUrl($p['estab_nome'], $p['estab_rua'], $p['estab_numero'], $p['estab_bairro'], $p['estab_cidade'], $p['estab_estado'], $p['estab_cep']);
            $zap  = whatsappUrl($p['estab_whatsapp'] ?? '', 'Olá! Tenho interesse em "' . $p['nome'] . '" pelo DataCerta.');
            $mesmoBairro = $clienteBairro && strtolower($p['estab_bairro']) === strtolower($clienteBairro) && strtolower($p['estab_cidade']) === strtolower($clienteCidade);
        ?>
        <article class="product-card"
                 data-cat="<?= sanitize($p['categoria']) ?>"
                 data-nome="<?= sanitize($p['nome']) ?>"
                 data-loja="<?= sanitize($p['estab_nome']) ?>"
                 onclick="abrirDetalheOferta(<?= $p['id'] ?>)">

            <div class="expiry-strip <?= $ec ?>"></div>

            <div class="card-img-wrap">
                <?php if (!empty($p['imagem']) && file_exists("uploads/".$p['imagem'])): ?>
                <img src="uploads/<?= sanitize($p['imagem']) ?>"
                     alt="<?= sanitize($p['nome']) ?>" loading="lazy">
                <?php else: ?>
                <div class="card-img-placeholder" style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-3)">
                    <?= categoriaLabel($p['categoria']) ?>
                </div>
                <?php endif; ?>
                <span class="discount-chip">-<?= $desc ?>%</span>
            </div>

            <div class="card-body">
                <div class="card-store">
                    <a href="perfil.php?id=<?= $p['estab_id'] ?>" onclick="event.stopPropagation()">
                        <?= sanitize($p['estab_nome']) ?>
                    </a>
                    <?php if (!$visitante): ?>
                    &nbsp;·&nbsp;<?= sanitize($p['estab_cidade']) ?>
                    <?php endif; ?>
                    <?php if ($mesmoBairro): ?>
                    <span class="badge badge-success" style="margin-left:4px;font-size:.62rem">Próximo</span>
                    <?php endif; ?>
                </div>
                <h3 class="card-name"><?= sanitize($p['nome']) ?></h3>
                <p class="card-desc"><?= sanitize($p['descricao']) ?></p>

                <div class="card-expiry-label <?= $ec ?>">
                    <?= $dias === 0 ? 'Vence hoje' : ($dias === 1 ? 'Vence amanhã' : "Vence em {$dias} dias") ?>
                </div>

                <!-- Localização -->
                <?php if ($visitante): ?>
                <div style="margin-bottom:12px">
                    <a href="login.php" onclick="event.stopPropagation()"
                       style="font-size:.72rem;font-weight:700;color:var(--brand)">
                        Entre para ver a localização
                    </a>
                </div>
                <?php elseif ($p['estab_bairro'] || $p['estab_cidade']): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <span style="font-size:.75rem;color:var(--txt-3)">
                        <?= sanitize(implode(', ', array_filter([$p['estab_bairro'], $p['estab_cidade']]))) ?>
                    </span>
                    <a href="<?= $maps ?>" target="_blank" rel="noopener" onclick="event.stopPropagation()"
                       style="font-size:.72rem;font-weight:700;color:var(--brand);white-space:nowrap;margin-left:8px">
                        Ver no mapa
                    </a>
                </div>
                <?php endif; ?>

                <div class="card-footer">
                    <div class="card-price">
                        <div class="price-from">R$ <?= number_format($p['preco_original'], 2, ',', '.') ?></div>
                        <div class="price-now">R$ <?= number_format($p['preco_promocional'], 2, ',', '.') ?></div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0" onclick="event.stopPropagation()">
                        <?php if (eTipo('cliente')): ?>
                        <?php if ($zap): ?>
                        <a href="<?= $zap ?>" target="_blank" rel="noopener" class="btn-add btn-whatsapp-mini" title="Falar no WhatsApp">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.52 0-10 4.48-10 10 0 1.76.46 3.48 1.34 5L2 22l5.15-1.35A9.96 9.96 0 0012.04 22c5.52 0 10-4.48 10-10s-4.48-10-10-10zm0 18.15a8.15 8.15 0 01-4.15-1.14l-.3-.18-3.06.8.82-2.98-.2-.31a8.12 8.12 0 01-1.25-4.34c0-4.49 3.65-8.14 8.14-8.14 4.49 0 8.14 3.65 8.14 8.14 0 4.49-3.65 8.15-8.14 8.15zm4.48-6.1c-.24-.12-1.43-.7-1.66-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.17-.7-.62-1.18-1.4-1.31-1.64-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.41h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.43-.58 1.63-1.15.2-.56.2-1.04.14-1.15-.06-.1-.22-.16-.46-.28z"/></svg>
                        </a>
                        <?php endif; ?>
                        <button class="btn-add" onclick="adicionarCarrinho(<?= $p['id'] ?>, this)">Carrinho</button>
                        <?php elseif ($visitante): ?>
                        <a href="login.php" class="btn-add" title="Entre para comprar">Carrinho</a>
                        <?php else: ?>
                        <span class="tag">Estoque: <?= $p['quantidade'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
        <div class="no-results" id="no-results" style="display:none">
            Nenhum produto nessa categoria.
        </div>
    </div>
    <?php endif; ?>
</div>

<section style="background:var(--surface);border-top:1px solid var(--border);padding:56px 0">
    <div class="container">
        <div style="text-align:center;margin-bottom:36px">
            <p class="section-label">Como funciona</p>
            <h2 style="font-size:1.6rem;font-weight:800;letter-spacing:-.03em">Simples, rápido e sustentável</h2>
        </div>
        <div class="steps-row">
            <div class="step-card">
                <div class="step-num">01</div>
                <h3>Encontre</h3>
                <p>Veja ofertas de estabelecimentos próximos e filtre pelo que você quer</p>
            </div>
            <div class="step-card">
                <div class="step-num">02</div>
                <h3>Adicione</h3>
                <p>Coloque produtos no carrinho e veja quanto você vai economizar</p>
            </div>
            <div class="step-card">
                <div class="step-num">03</div>
                <h3>Finalize</h3>
                <p>Confirme o pedido e retire no estabelecimento na hora combinada</p>
            </div>
            <div class="step-card">
                <div class="step-num">04</div>
                <h3>Impacto real</h3>
                <p>Cada compra evita desperdício e contribui para um mundo mais sustentável</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
