<?php
require_once 'conexao.php';
exigeLogin('cliente');
$titulo = 'Meus Pedidos';
$cId = $_SESSION['usuario_id'];

$pedidos = $pdo->prepare("
    SELECT p.*, e.nome AS estab_nome, e.id AS estab_id,
           (SELECT nota FROM avaliacoes WHERE pedido_id = p.id AND cliente_id = ? LIMIT 1) AS minha_nota
    FROM pedidos p JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE p.cliente_id = ? ORDER BY p.created_at DESC
");
$pedidos->execute([$cId, $cId]);
$pedidos = $pedidos->fetchAll();

$itensMap = [];
if ($pedidos) {
    $ids = array_column($pedidos, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $s = $pdo->prepare("SELECT ip.pedido_id, ip.quantidade, ip.preco_unitario, pr.nome FROM itens_pedido ip JOIN produtos pr ON ip.produto_id = pr.id WHERE ip.pedido_id IN ($in)");
    $s->execute($ids);
    foreach ($s->fetchAll() as $r) $itensMap[$r['pedido_id']][] = $r;
}

$suc = $_SESSION['flash_sucesso'] ?? ''; unset($_SESSION['flash_sucesso']);
include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Meus Pedidos</h1>
        <p><?= count($pedidos) ?> pedido(s) realizados</p>
    </div>
</div>

<div class="container section">
    <?php if ($suc): ?><div class="alert alert-success alert-auto"><?= sanitize($suc) ?></div><?php endif; ?>

    <?php if (empty($pedidos)): ?>
    <div class="card">
        <div class="empty" style="padding:70px 20px">
            <h3>Nenhum pedido ainda</h3>
            <p>Você ainda não realizou nenhuma compra.</p>
            <a href="index.php" class="btn btn-primary">Ver ofertas</a>
        </div>
    </div>
    <?php else: ?>
    <?php
    $badges = ['pendente'=>'badge-warning','confirmado'=>'badge-info','entregue'=>'badge-success','cancelado'=>'badge-danger'];
    $labels = ['pendente'=>'Pendente','confirmado'=>'Confirmado','entregue'=>'Entregue','cancelado'=>'Cancelado'];
    foreach ($pedidos as $p): ?>
    <div class="card" style="margin-bottom:16px">
        <div style="padding:20px 22px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px">
                <div>
                    <div style="font-size:.75rem;color:var(--txt-3);margin-bottom:4px">
                        Pedido #<?= $p['id'] ?> · <?= date('d/m/Y \à\s H:i', strtotime($p['created_at'])) ?>
                    </div>
                    <div style="font-size:1rem;font-weight:700">
                        <a href="perfil.php?id=<?= $p['estab_id'] ?>" style="color:var(--brand)"><?= sanitize($p['estab_nome']) ?></a>
                    </div>
                </div>
                <div style="text-align:right">
                    <span class="badge <?= $badges[$p['status']] ?? 'badge-neutral' ?>" style="padding:5px 12px;font-size:.78rem"><?= $labels[$p['status']] ?? $p['status'] ?></span>
                    <div style="font-size:1.15rem;font-weight:800;color:var(--green);margin-top:6px">R$ <?= number_format($p['total'],2,',','.') ?></div>
                </div>
            </div>

            <?php if (!empty($itensMap[$p['id']])): ?>
            <div style="background:var(--surface-2);border-radius:var(--r2);padding:12px 14px;margin-bottom:14px">
                <?php foreach ($itensMap[$p['id']] as $it): ?>
                <div style="font-size:.82rem;color:var(--txt-2);margin-bottom:3px">
                    · <?= sanitize($it['nome']) ?> ×<?= $it['quantidade'] ?> — R$ <?= number_format($it['preco_unitario'],2,',','.') ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <?php if (empty($p['minha_nota'])): ?>
                <a href="avaliar.php?estab_id=<?= $p['estab_id'] ?>&pedido_id=<?= $p['id'] ?>"
                   class="btn btn-ghost btn-sm">Avaliar</a>
                <?php else: ?>
                <span style="font-size:.8rem;color:var(--amber);font-weight:600;display:flex;align-items:center;gap:4px">
                    <?= str_repeat('★', (int)$p['minha_nota']) ?><?= str_repeat('☆', 5-(int)$p['minha_nota']) ?>
                    Você avaliou
                </span>
                <?php endif; ?>
                <a href="perfil.php?id=<?= $p['estab_id'] ?>" class="btn btn-secondary btn-sm">Ver estabelecimento</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
