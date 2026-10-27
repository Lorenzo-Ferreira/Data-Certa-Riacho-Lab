<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$titulo = 'Vendas';
$eId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pedido_id'])) {
    $ns = $_POST['status'] ?? '';
    if (in_array($ns, ['pendente','confirmado','entregue','cancelado'])) {
        $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ? AND estabelecimento_id = ?")->execute([$ns,$_POST['pedido_id'],$eId]);
    }
    redirect('vendas.php');
}

$pedidos = $pdo->prepare("SELECT p.*, c.nome AS cname FROM pedidos p JOIN clientes c ON p.cliente_id = c.id WHERE p.estabelecimento_id = ? ORDER BY p.created_at DESC");
$pedidos->execute([$eId]); $pedidos = $pedidos->fetchAll();

$itMap = [];
if ($pedidos) {
    $ids = array_column($pedidos,'id'); $in = implode(',',array_fill(0,count($ids),'?'));
    $s = $pdo->prepare("SELECT ip.pedido_id,ip.quantidade,ip.preco_unitario,pr.nome FROM itens_pedido ip JOIN produtos pr ON ip.produto_id=pr.id WHERE ip.pedido_id IN ($in)");
    $s->execute($ids);
    foreach ($s->fetchAll() as $r) $itMap[$r['pedido_id']][] = $r;
}

$badges = ['pendente'=>'badge-warning','confirmado'=>'badge-info','entregue'=>'badge-success','cancelado'=>'badge-danger'];
$labels = ['pendente'=>'Pendente','confirmado'=>'Confirmado','entregue'=>'Entregue','cancelado'=>'Cancelado'];
include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Vendas</h1>
        <p><?= count($pedidos) ?> pedido(s) recebido(s)</p>
    </div>
</div>

<div class="container section">
    <?php if (empty($pedidos)): ?>
    <div class="card">
        <div class="empty" style="padding:70px 20px">
            <h3>Nenhuma venda ainda</h3>
            <p>Quando clientes fizerem pedidos, aparecerão aqui.</p>
            <a href="produto_form.php" class="btn btn-primary">+ Cadastrar produto</a>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($pedidos as $p): ?>
    <div class="card" style="margin-bottom:14px">
        <div style="padding:20px 22px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px">
                <div>
                    <div style="font-size:.75rem;color:var(--txt-3);margin-bottom:3px">Pedido #<?= $p['id'] ?> · <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
                    <div style="font-weight:700"><?= sanitize($p['cname']) ?></div>
                </div>
                <div style="text-align:right">
                    <span class="badge <?= $badges[$p['status']] ?? 'badge-neutral' ?>"><?= $labels[$p['status']] ?? $p['status'] ?></span>
                    <div style="font-size:1.15rem;font-weight:800;color:var(--green);margin-top:5px">R$ <?= number_format($p['total'],2,',','.') ?></div>
                </div>
            </div>

            <?php if (!empty($itMap[$p['id']])): ?>
            <div style="background:var(--surface-2);border-radius:var(--r2);padding:10px 14px;margin-bottom:14px">
                <?php foreach ($itMap[$p['id']] as $it): ?>
                <div style="font-size:.82rem;color:var(--txt-2)">· <?= sanitize($it['nome']) ?> ×<?= $it['quantidade'] ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                <label style="font-size:.78rem;font-weight:700;color:var(--txt-3);text-transform:uppercase;letter-spacing:.04em">Status</label>
                <select name="status" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:var(--r2);font-size:.85rem;background:var(--surface);color:var(--txt);outline:none">
                    <?php foreach ($labels as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $p['status']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Salvar</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
