<?php
require_once 'conexao.php';
exigeLogin('cliente');
$titulo = 'Carrinho';
$cId = $_SESSION['usuario_id'];

// Limpar itens expirados/zerados
$pdo->prepare("DELETE c FROM carrinho c JOIN produtos p ON c.produto_id = p.id WHERE c.cliente_id = ? AND (p.data_validade < CURDATE() OR p.quantidade = 0)")->execute([$cId]);

$itens = $pdo->prepare("
    SELECT c.id AS iid, c.quantidade,
           p.id AS pid, p.nome, p.imagem, p.categoria,
           p.preco_original, p.preco_promocional, p.quantidade AS estoque,
           p.data_validade,
           e.nome AS estab_nome, e.id AS estab_id
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.id
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE c.cliente_id = ? AND p.data_validade >= CURDATE() AND p.quantidade > 0
    ORDER BY e.nome, p.nome
");
$itens->execute([$cId]);
$itens = $itens->fetchAll();

$subtotal = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco_promocional'], $itens));
$economia = array_sum(array_map(fn($i) => $i['quantidade'] * ($i['preco_original'] - $i['preco_promocional']), $itens));

function ec($cat) { return strtoupper(substr($cat,0,3)); }
include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Carrinho</h1>
        <p><?= count($itens) ?> item(s) adicionado(s)</p>
    </div>
</div>

<div class="container section">
    <?php if (empty($itens)): ?>
    <div class="card">
        <div class="empty" style="padding:70px 20px">
            <h3>Carrinho vazio</h3>
            <p>Você ainda não adicionou produtos. Confira as ofertas disponíveis.</p>
            <a href="index.php" class="btn btn-primary">Ver ofertas</a>
        </div>
    </div>
    <?php else: ?>
    <div class="cart-layout">
        <!-- ITENS -->
        <div>
            <?php foreach ($itens as $it): ?>
            <div class="cart-item" id="cart-item-<?= $it['iid'] ?>">
                <div class="cart-item-thumb">
                    <?php if (!empty($it['imagem']) && file_exists("uploads/".$it['imagem'])): ?>
                    <img src="uploads/<?= sanitize($it['imagem']) ?>" alt="">
                    <?php else: ?>
                    <?= ec($it['categoria']) ?>
                    <?php endif; ?>
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name"><?= sanitize($it['nome']) ?></div>
                    <div class="cart-item-store"><?= sanitize($it['estab_nome']) ?></div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                        <div class="cart-item-price">R$ <?= number_format($it['preco_promocional'],2,',','.') ?></div>
                        <span style="font-size:.75rem;color:var(--txt-3);text-decoration:line-through">R$ <?= number_format($it['preco_original'],2,',','.') ?></span>
                        <span style="font-size:.72rem;color:var(--amber);font-weight:600">Validade: <?= date('d/m/Y', strtotime($it['data_validade'])) ?></span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:12px">
                    <div class="qty-ctrl">
                        <button class="qty-btn" onclick="alterarQtd(<?= $it['iid'] ?>, -1)">−</button>
                        <input type="number" id="qty-<?= $it['iid'] ?>" class="qty-val"
                               value="<?= $it['quantidade'] ?>" min="1" max="<?= $it['estoque'] ?>"
                               oninput="clampQty(this)" onchange="atualizarCarrinho(<?= $it['iid'] ?>, this.value)">
                        <button class="qty-btn" onclick="alterarQtd(<?= $it['iid'] ?>, 1)">+</button>
                    </div>
                    <button onclick="removerItem(<?= $it['iid'] ?>)"
                            style="background:none;border:none;color:var(--brand);cursor:pointer;font-size:.78rem;font-weight:600">
                        Remover
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- RESUMO -->
        <div class="cart-summary">
            <h3>Resumo</h3>
            <div class="summary-row">
                <span>Subtotal (<?= count($itens) ?> itens)</span>
                <span id="cart-subtotal">R$ <?= number_format($subtotal,2,',','.') ?></span>
            </div>
            <div class="summary-row" style="color:var(--green)">
                <span>Sua economia</span>
                <span>R$ <?= number_format($economia,2,',','.') ?></span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span id="cart-total">R$ <?= number_format($subtotal,2,',','.') ?></span>
            </div>
            <a href="finalizar.php" class="btn btn-primary btn-block btn-lg" style="margin-top:16px">
                Finalizar compra
            </a>
            <a href="index.php" class="btn btn-secondary btn-block" style="margin-top:8px;text-align:center">
                ← Continuar comprando
            </a>
            <div style="margin-top:16px;background:var(--green-wash);border-radius:var(--r2);padding:12px;font-size:.78rem;color:var(--green);text-align:center;font-weight:600">
                Cada compra evita desperdicio de alimentos
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
