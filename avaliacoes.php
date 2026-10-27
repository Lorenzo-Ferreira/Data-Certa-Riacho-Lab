<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$titulo = 'Avaliações';
$eId = $_SESSION['usuario_id'];

$s = $pdo->prepare("SELECT ROUND(AVG(nota),1), COUNT(*) FROM avaliacoes WHERE estabelecimento_id = ?"); $s->execute([$eId]); [$media,$total] = $s->fetch(PDO::FETCH_NUM);
$dist = [];
for ($i=1;$i<=5;$i++) { $s = $pdo->prepare("SELECT COUNT(*) FROM avaliacoes WHERE estabelecimento_id = ? AND nota = ?"); $s->execute([$eId,$i]); $dist[$i] = (int)$s->fetchColumn(); }

$avs = $pdo->prepare("SELECT a.*, c.nome AS cname FROM avaliacoes a JOIN clientes c ON a.cliente_id = c.id WHERE a.estabelecimento_id = ? ORDER BY a.created_at DESC");
$avs->execute([$eId]); $avs = $avs->fetchAll();
include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Avaliações</h1>
        <p>O que seus clientes estão dizendo sobre o seu estabelecimento</p>
    </div>
</div>

<div class="container section">
    <!-- RESUMO -->
    <div class="card" style="margin-bottom:24px">
        <div class="card-body-pad">
            <div style="display:grid;grid-template-columns:auto 1fr;gap:40px;align-items:center">
                <div style="text-align:center;min-width:120px">
                    <div style="font-size:3.5rem;font-weight:800;letter-spacing:-.04em;line-height:1"><?= $media ?: '—' ?></div>
                    <div style="font-size:1.3rem;color:var(--amber);margin:6px 0">
                        <?php if ($media): for ($i=1;$i<=5;$i++) echo $i<=round($media)?'★':'☆'; endif; ?>
                    </div>
                    <div style="font-size:.78rem;color:var(--txt-3)"><?= $total ?> avaliação(ões)</div>
                </div>
                <div>
                    <?php for ($i=5;$i>=1;$i--): $pct = $total > 0 ? ($dist[$i]/$total*100) : 0; ?>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <span style="font-size:.78rem;font-weight:700;color:var(--txt-3);min-width:16px"><?= $i ?>★</span>
                        <div style="flex:1;height:8px;background:var(--surface-2);border-radius:var(--pill);overflow:hidden">
                            <div style="width:<?= $pct ?>%;height:100%;background:var(--amber);border-radius:var(--pill);transition:width .4s"></div>
                        </div>
                        <span style="font-size:.75rem;color:var(--txt-3);min-width:18px;text-align:right"><?= $dist[$i] ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA -->
    <?php if (empty($avs)): ?>
    <div class="card">
        <div class="empty" style="padding:60px 20px">
            <h3>Nenhuma avaliação ainda</h3>
            <p>Quando clientes avaliarem, aparecerão aqui.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($avs as $av): ?>
    <div class="review-card">
        <div class="review-top">
            <div>
                <div class="reviewer"><?= sanitize($av['cname']) ?></div>
                <div class="stars-display sm">
                    <?= str_repeat('★', $av['nota']) ?><?= str_repeat('☆', 5-$av['nota']) ?>
                    <span style="font-size:.72rem;color:var(--txt-3);margin-left:4px"><?= $av['nota'] ?>/5</span>
                </div>
            </div>
            <div class="review-date"><?= date('d/m/Y', strtotime($av['created_at'])) ?></div>
        </div>
        <?php if ($av['comentario']): ?>
        <div class="review-text">"<?= sanitize($av['comentario']) ?>"</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
