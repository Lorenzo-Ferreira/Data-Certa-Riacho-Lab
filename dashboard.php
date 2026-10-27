<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$titulo = 'Dashboard';
$eId = $_SESSION['usuario_id'];

$totalVendas = (float)$pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estabelecimento_id = ?")->execute([$eId]) ? null : 0;
$s = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estabelecimento_id = ?"); $s->execute([$eId]); $totalVendas = (float)$s->fetchColumn();
$s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE estabelecimento_id = ?"); $s->execute([$eId]); $totalPedidos = (int)$s->fetchColumn();
$s = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE estabelecimento_id = ? AND data_validade >= CURDATE() AND ativo = 1 AND quantidade > 0"); $s->execute([$eId]); $prodAtivos = (int)$s->fetchColumn();
$s = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE estabelecimento_id = ? AND (data_validade < CURDATE() OR quantidade = 0)"); $s->execute([$eId]); $prodExp = (int)$s->fetchColumn();
$s = $pdo->prepare("SELECT ROUND(AVG(nota),1) FROM avaliacoes WHERE estabelecimento_id = ?"); $s->execute([$eId]); $mediaAval = $s->fetchColumn() ?? 0;

$ultVendas = $pdo->prepare("
    SELECT p.id, p.total, p.status, p.created_at, c.nome AS cliente_nome
    FROM pedidos p JOIN clientes c ON p.cliente_id = c.id
    WHERE p.estabelecimento_id = ? ORDER BY p.created_at DESC LIMIT 6
");
$ultVendas->execute([$eId]);
$ultVendas = $ultVendas->fetchAll();

// Gráfico 7 dias
$graf = $pdo->prepare("
    SELECT DATE(created_at) AS dia, COALESCE(SUM(total),0) AS val
    FROM pedidos WHERE estabelecimento_id = ?
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY dia ORDER BY dia
");
$graf->execute([$eId]);
$grafRows = $graf->fetchAll();

$labels = []; $vals = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $labels[] = date('d/m', strtotime($d));
    $v = 0;
    foreach ($grafRows as $r) { if ($r['dia'] === $d) { $v = $r['val']; break; } }
    $vals[] = (float)$v;
}

include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Dashboard</h1>
        <p>Olá, <strong><?= sanitize(explode(' ', $_SESSION['usuario_nome'])[0]) ?></strong> — resumo do seu estabelecimento</p>
    </div>
</div>

<div class="container section">

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-tile accent-brand">
            <div class="stat-tile-ico"></div>
            <div class="stat-tile-val">R$ <?= number_format($totalVendas, 2, ',', '.') ?></div>
            <div class="stat-tile-label">Total em vendas</div>
        </div>
        <div class="stat-tile accent-blue">
            <div class="stat-tile-ico"></div>
            <div class="stat-tile-val"><?= $totalPedidos ?></div>
            <div class="stat-tile-label">Pedidos recebidos</div>
        </div>
        <div class="stat-tile accent-green">
            <div class="stat-tile-ico"></div>
            <div class="stat-tile-val"><?= $prodAtivos ?></div>
            <div class="stat-tile-label">Produtos ativos</div>
        </div>
        <div class="stat-tile accent-amber">
            <div class="stat-tile-ico"></div>
            <div class="stat-tile-val"><?= $prodExp ?></div>
            <div class="stat-tile-label">Expirados / zerados</div>
        </div>
        <div class="stat-tile accent-amber">
            <div class="stat-tile-ico"></div>
            <div class="stat-tile-val"><?= $mediaAval ?: '—' ?></div>
            <div class="stat-tile-label">Avaliação média</div>
        </div>
    </div>

    <!-- AÇÕES RÁPIDAS -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px">
        <a href="produto_form.php" class="btn btn-primary">+ Novo produto</a>
        <a href="produtos.php" class="btn btn-secondary">Gerenciar produtos</a>
        <a href="vendas.php" class="btn btn-secondary">Ver vendas</a>
        <a href="avaliacoes.php" class="btn btn-secondary">Avaliações</a>
    </div>

    <!-- GRÁFICO -->
    <div class="chart-card">
        <h3>Vendas — últimos 7 dias</h3>
        <canvas id="grafVendas" height="70"></canvas>
    </div>

    <!-- ÚLTIMAS VENDAS -->
    <div class="card">
        <div class="card-header">
            <h3>Últimas vendas</h3>
            <a href="vendas.php" class="btn btn-secondary btn-sm">Ver todas</a>
        </div>
        <?php if (empty($ultVendas)): ?>
        <div class="empty" style="padding:40px 20px">
            <h3>Nenhuma venda ainda</h3>
            <p><a href="produto_form.php" style="color:var(--brand)">Cadastre seu primeiro produto</a> para começar a vender</p>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Cliente</th><th>Total</th><th>Status</th><th>Data</th></tr>
                </thead>
                <tbody>
                    <?php
                    $badges = ['pendente'=>'badge-warning','confirmado'=>'badge-info','entregue'=>'badge-success','cancelado'=>'badge-danger'];
                    $labels2 = ['pendente'=>'Pendente','confirmado'=>'Confirmado','entregue'=>'Entregue','cancelado'=>'Cancelado'];
                    foreach ($ultVendas as $v): ?>
                    <tr>
                        <td style="color:var(--txt-3);font-size:.8rem">#<?= $v['id'] ?></td>
                        <td><?= sanitize($v['cliente_nome']) ?></td>
                        <td><strong>R$ <?= number_format($v['total'], 2, ',', '.') ?></strong></td>
                        <td><span class="badge <?= $badges[$v['status']] ?? 'badge-neutral' ?>"><?= $labels2[$v['status']] ?? $v['status'] ?></span></td>
                        <td style="color:var(--txt-3);font-size:.8rem"><?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
const tickColor = isDark ? '#5c504a' : '#9c8880';

new Chart(document.getElementById('grafVendas'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Vendas (R$)',
            data: <?= json_encode($vals) ?>,
            backgroundColor: '#e86961cc',
            borderColor: '#e86961',
            borderWidth: 1.5,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: tickColor } },
            y: {
                beginAtZero: true,
                grid: { color: gridColor },
                ticks: {
                    color: tickColor,
                    callback: v => 'R$' + v.toFixed(0)
                }
            }
        }
    }
});
</script>
