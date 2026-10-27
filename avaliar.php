<?php
require_once 'conexao.php';
exigeLogin('cliente');
$titulo = 'Avaliar';
$cId = $_SESSION['usuario_id'];

$eId = (int)($_GET['estab_id'] ?? 0);
$pId = (int)($_GET['pedido_id'] ?? 0);
if (!$eId) redirect('pedidos.php');

$estab = $pdo->prepare("SELECT * FROM estabelecimentos WHERE id = ?"); $estab->execute([$eId]); $estab = $estab->fetch();
if (!$estab) redirect('pedidos.php');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = (int)($_POST['nota'] ?? 0);
    $coment = trim($_POST['comentario'] ?? '');
    if ($nota < 1 || $nota > 5) { $erro = 'Selecione uma nota de 1 a 5.'; }
    else {
        $pdo->prepare("INSERT INTO avaliacoes (cliente_id,estabelecimento_id,pedido_id,nota,comentario) VALUES (?,?,?,?,?)")
            ->execute([$cId,$eId,$pId ?: null,$nota,$coment]);
        $_SESSION['flash_sucesso'] = 'Avaliação enviada! Obrigado pelo feedback.';
        redirect('pedidos.php');
    }
}
include 'header.php';
?>
<div class="container" style="padding-top:40px;padding-bottom:60px">
    <div class="form-wrap" style="max-width:480px">
        <div class="form-head">
            <h1>Avaliar estabelecimento</h1>
            <p><?= sanitize($estab['nome']) ?></p>
        </div>

        <?php if ($erro): ?><div class="alert alert-error"><?= sanitize($erro) ?></div><?php endif; ?>

        <form method="POST">
            <div class="field" style="text-align:center">
                <label style="display:block;margin-bottom:16px">Selecione uma nota</label>
                <div class="star-input-row">
                    <?php for ($i=1;$i<=5;$i++): ?>
                    <button type="button" class="star-input-btn" onclick="setRating(<?= $i ?>)">☆</button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="nota" id="nota_val" value="0">
                <div id="rating-label" style="font-size:.9rem;font-weight:700;color:var(--amber);height:20px;margin-top:6px"></div>
            </div>
            <div class="field" style="margin-top:8px">
                <label>Comentário (opcional)</label>
                <textarea name="comentario" placeholder="Como foi sua experiência?..."><?= sanitize($_POST['comentario'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Enviar avaliação</button>
            <a href="pedidos.php" class="btn btn-secondary btn-block" style="margin-top:8px;text-align:center">Cancelar</a>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
