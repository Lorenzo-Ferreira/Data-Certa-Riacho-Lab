<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$titulo = 'Meus Produtos';
$eId = $_SESSION['usuario_id'];

$filtro = $_GET['filtro'] ?? 'ativos';
$where = match($filtro) {
    'expirados' => "AND (data_validade < CURDATE() OR quantidade = 0)",
    'todos'     => "",
    default     => "AND data_validade >= CURDATE() AND quantidade > 0 AND ativo = 1",
};

$stmt = $pdo->prepare("
    SELECT *, DATEDIFF(data_validade, CURDATE()) AS dias
    FROM produtos WHERE estabelecimento_id = ? $where
    ORDER BY data_validade ASC
");
$stmt->execute([$eId]);
$produtos = $stmt->fetchAll();

$suc = $_SESSION['flash_sucesso'] ?? ''; $err = $_SESSION['flash_erro'] ?? '';
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

function emoji2($cat) { return strtoupper(substr($cat,0,3)); }

include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Meus Produtos</h1>
        <p>Cadastre e gerencie os produtos com desconto do seu estabelecimento</p>
    </div>
</div>

<div class="container section">
    <?php if ($suc): ?><div class="alert alert-success alert-auto"><?= sanitize($suc) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error alert-auto"><?= sanitize($err) ?></div><?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:22px">
        <!-- Tabs filtro -->
        <div style="display:flex;gap:8px">
            <?php foreach (['ativos'=>'Ativos','expirados'=>'Expirados','todos'=>'Todos'] as $v=>$l): ?>
            <a href="?filtro=<?= $v ?>"
               class="btn btn-sm <?= $filtro === $v ? 'btn-primary' : 'btn-secondary' ?>">
                <?= $l ?>
            </a>
            <?php endforeach; ?>
        </div>
        <a href="produto_form.php" class="btn btn-primary">+ Novo produto</a>
    </div>

    <?php if (empty($produtos)): ?>
    <div class="card">
        <div class="empty" style="padding:60px 20px">
            <h3><?= $filtro === 'ativos' ? 'Nenhum produto ativo' : 'Nenhum produto' ?></h3>
            <p><?= $filtro === 'ativos' ? 'Cadastre produtos para começar a vender com desconto.' : 'Nenhum produto nesta categoria.' ?></p>
            <a href="produto_form.php" class="btn btn-primary">+ Cadastrar produto</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Cat.</th>
                        <th>Estoque</th>
                        <th>Original</th>
                        <th>Promocional</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p):
                        $dias = (int)$p['dias'];
                        $venc = $dias < 0;
                        $zero = $p['quantidade'] == 0;
                        $urg  = !$venc && $dias <= 1;
                    ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;background:var(--surface-2);border-radius:var(--r1);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.02em;color:var(--txt-3);border:1px solid var(--border)">
                                    <?= emoji2($p['categoria']) ?>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:.875rem"><?= sanitize($p['nome']) ?></div>
                                    <?php if ($p['descricao']): ?>
                                    <div style="font-size:.75rem;color:var(--txt-3)"><?= sanitize(substr($p['descricao'],0,40)) ?>…</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="tag"><?= sanitize($p['categoria']) ?></span></td>
                        <td style="font-weight:600"><?= $p['quantidade'] ?></td>
                        <td style="color:var(--txt-3);text-decoration:line-through;font-size:.85rem">
                            R$ <?= number_format($p['preco_original'],2,',','.') ?>
                        </td>
                        <td style="font-weight:700;color:var(--green)">
                            R$ <?= number_format($p['preco_promocional'],2,',','.') ?>
                        </td>
                        <td>
                            <div style="font-size:.825rem"><?= date('d/m/Y', strtotime($p['data_validade'])) ?></div>
                            <?php if ($venc || $zero): ?>
                                <span class="badge badge-danger" style="margin-top:3px"><?= $venc ? 'Vencido' : 'Zerado' ?></span>
                            <?php elseif ($urg): ?>
                                <span class="badge badge-warning" style="margin-top:3px"><?= $dias === 0 ? 'Vence hoje' : 'Amanhã' ?></span>
                            <?php else: ?>
                                <span style="font-size:.72rem;color:var(--txt-3)"><?= $dias ?> dias</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($zero): ?>
                                <span class="badge badge-danger">Zerado</span>
                            <?php elseif ($venc): ?>
                                <span class="badge badge-danger">Vencido</span>
                            <?php elseif ($p['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-neutral">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <a href="produto_form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                                <button onclick="confirmarExclusao('Excluir este produto?','produto_deletar.php?id=<?= $p['id'] ?>')"
                                        class="btn btn-danger btn-sm">Excluir</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
