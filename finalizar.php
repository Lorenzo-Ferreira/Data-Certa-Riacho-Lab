<?php
require_once 'conexao.php';
exigeLogin('cliente');
$titulo = 'Finalizar Compra';
$cId = $_SESSION['usuario_id'];

$itens = $pdo->prepare("
    SELECT c.id AS iid, c.quantidade,
           p.id AS pid, p.nome, p.preco_promocional, p.quantidade AS estoque,
           p.data_validade, p.estabelecimento_id,
           e.nome AS estab_nome, e.chave_pix
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.id
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE c.cliente_id = ? AND p.data_validade >= CURDATE() AND p.quantidade > 0 AND p.ativo = 1
");
$itens->execute([$cId]);
$itens = $itens->fetchAll();
if (empty($itens)) redirect('carrinho.php');

$total = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco_promocional'], $itens));
$cliente = $pdo->prepare("SELECT * FROM clientes WHERE id = ?"); $cliente->execute([$cId]); $cliente = $cliente->fetch();

// Agrupa por estabelecimento (usado antes e depois da confirmação)
$porEstab = [];
foreach ($itens as $it) {
    $eid = $it['estabelecimento_id'];
    if (!isset($porEstab[$eid])) {
        $porEstab[$eid] = [
            'nome'      => $it['estab_nome'],
            'chave_pix' => $it['chave_pix'],
            'total'     => 0,
            'itens'     => [],
        ];
    }
    $porEstab[$eid]['total'] += $it['quantidade'] * $it['preco_promocional'];
    $porEstab[$eid]['itens'][] = $it;
}

$sucesso = $erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        foreach ($porEstab as $eid => $g) {
            foreach ($g['itens'] as $it) {
                if ($it['quantidade'] > $it['estoque']) throw new Exception("Estoque insuficiente: {$it['nome']}");
            }
            $pdo->prepare("INSERT INTO pedidos (cliente_id,estabelecimento_id,total) VALUES (?,?,?)")->execute([$cId,$eid,$g['total']]);
            $pid = $pdo->lastInsertId();
            foreach ($g['itens'] as $it) {
                $pdo->prepare("INSERT INTO itens_pedido (pedido_id,produto_id,quantidade,preco_unitario) VALUES (?,?,?,?)")->execute([$pid,$it['pid'],$it['quantidade'],$it['preco_promocional']]);
                $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?")->execute([$it['quantidade'],$it['pid']]);
                $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ? AND quantidade = 0")->execute([$it['pid']]);
            }
        }
        $pdo->prepare("DELETE FROM carrinho WHERE cliente_id = ?")->execute([$cId]);
        $pdo->commit();
        $sucesso = 'ok';
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = $e->getMessage();
    }
}

include 'header.php';
?>
<div class="container section" style="max-width:700px;margin-left:auto;margin-right:auto">

<?php if ($sucesso === 'ok'): ?>

    <!-- Confirmação -->
    <div class="card" style="text-align:center;padding:52px 30px 36px;margin-bottom:16px">
        <div style="width:72px;height:72px;margin:0 auto 18px;border-radius:50%;background:var(--green-wash);color:var(--green);display:flex;align-items:center;justify-content:center">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h2 style="font-size:1.75rem;font-weight:800;letter-spacing:-.04em;margin-bottom:10px">Pedido confirmado!</h2>
        <p style="color:var(--txt-2);max-width:400px;margin:0 auto">
            Obrigado pela compra. Você ajudou a reduzir o desperdício de alimentos.
        </p>
    </div>

    <!-- Instruções de pagamento por estabelecimento -->
    <?php foreach ($porEstab as $eid => $g): ?>
    <div class="card" style="margin-bottom:14px">
        <div class="card-header">
            <div>
                <h3>Como pagar</h3>
                <?php if (count($porEstab) > 1): ?>
                <div class="payment-estab-name"><?= sanitize($g['nome']) ?></div>
                <?php endif; ?>
            </div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--green)">
                R$ <?= number_format($g['total'],2,',','.') ?>
            </div>
        </div>
        <div class="card-body-pad">
            <?php if (!empty($g['chave_pix'])): ?>
            <div class="pix-block">
                <div class="pix-badge">Pix</div>
                <div class="pix-key-wrap">
                    <div class="pix-key-label">Chave Pix</div>
                    <div class="pix-key"><?= sanitize($g['chave_pix']) ?></div>
                </div>
                <button class="btn-copy" data-pix="<?= sanitize($g['chave_pix']) ?>" onclick="copiarPix(this)">
                    Copiar
                </button>
            </div>
            <div class="pix-amount">
                <span class="pix-amount-label">Valor a transferir</span>
                <span class="pix-amount-val">R$ <?= number_format($g['total'],2,',','.') ?></span>
            </div>
            <p style="font-size:.78rem;color:var(--txt-3);margin-top:12px;margin-bottom:0">
                Realize o pagamento antes de ir buscar o produto. Leve o comprovante.
            </p>
            <?php else: ?>
            <div class="pickup-block">
                <div class="pickup-ico" style="color:var(--amber)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5c0-1.4 1.1-2.5 2.5-2.5s2.5 1 2.5 2c0 1.5-2.5 1.7-2.5 3.5M12 17h.01"/></svg></div>
                <div class="pickup-info">
                    <strong>Pagamento na retirada</strong>
                    <span>Dinheiro ou cartão aceitos no local.</span>
                </div>
                <div style="font-size:1.05rem;font-weight:800;color:var(--green);white-space:nowrap">
                    R$ <?= number_format($g['total'],2,',','.') ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:8px">
        <a href="pedidos.php" class="btn btn-primary">Ver meus pedidos</a>
        <a href="index.php" class="btn btn-secondary">Continuar comprando</a>
    </div>

<?php else: ?>

    <?php if ($erro): ?><div class="alert alert-error"><?= sanitize($erro) ?></div><?php endif; ?>

    <!-- Revisão do pedido -->
    <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>Revisão do pedido</h3></div>
        <div style="padding:0 22px">
            <?php foreach ($itens as $it): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-weight:700;font-size:.9rem"><?= sanitize($it['nome']) ?></div>
                    <div style="font-size:.75rem;color:var(--txt-3)"><?= sanitize($it['estab_nome']) ?> · <?= $it['quantidade'] ?>×</div>
                </div>
                <div style="font-weight:700;color:var(--green)">
                    R$ <?= number_format($it['quantidade'] * $it['preco_promocional'],2,',','.') ?>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;padding:16px 0;font-size:1.1rem;font-weight:800">
                <span>Total</span>
                <span style="color:var(--green)">R$ <?= number_format($total,2,',','.') ?></span>
            </div>
        </div>
    </div>

    <!-- Dados do cliente -->
    <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>Seus dados</h3></div>
        <div class="card-body-pad" style="font-size:.875rem;line-height:2;color:var(--txt-2)">
            <strong style="color:var(--txt)"><?= sanitize($cliente['nome']) ?></strong> · <?= sanitize($cliente['email']) ?>
            <?php if ($cliente['telefone']): ?> · <?= sanitize($cliente['telefone']) ?><?php endif; ?>
            <?php if ($cliente['cidade']): ?><br><?= sanitize($cliente['cidade']) ?><?= $cliente['bairro'] ? ', '.sanitize($cliente['bairro']) : '' ?><?php endif; ?>
        </div>
    </div>

    <!-- Pagamento por estabelecimento -->
    <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>Pagamento</h3></div>
        <div class="card-body-pad" style="display:flex;flex-direction:column;gap:14px">
            <?php foreach ($porEstab as $eid => $g): ?>
            <div>
                <?php if (count($porEstab) > 1): ?>
                <div class="payment-estab-label"><?= sanitize($g['nome']) ?></div>
                <?php endif; ?>
                <?php if (!empty($g['chave_pix'])): ?>
                <div class="pix-block">
                    <div class="pix-badge">Pix</div>
                    <div class="pix-key-wrap">
                        <div class="pix-key-label">Chave Pix</div>
                        <div class="pix-key"><?= sanitize($g['chave_pix']) ?></div>
                    </div>
                    <button type="button" class="btn-copy" data-pix="<?= sanitize($g['chave_pix']) ?>" onclick="copiarPix(this)">
                        Copiar
                    </button>
                </div>
                <div class="pix-amount">
                    <span class="pix-amount-label">Valor</span>
                    <span class="pix-amount-val">R$ <?= number_format($g['total'],2,',','.') ?></span>
                </div>
                <?php else: ?>
                <div class="pickup-block">
                    <div class="pickup-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5c0-1.4 1.1-2.5 2.5-2.5s2.5 1 2.5 2c0 1.5-2.5 1.7-2.5 3.5M12 17h.01"/></svg></div>
                    <div class="pickup-info">
                        <strong>Pagamento na retirada</strong>
                        <span>Dinheiro ou cartão aceitos no local.</span>
                    </div>
                    <div style="font-size:1.05rem;font-weight:800;color:var(--green);white-space:nowrap">
                        R$ <?= number_format($g['total'],2,',','.') ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="alert alert-warning" style="margin-bottom:18px">
        Retirada presencial. Verifique o horario de funcionamento antes de ir.
    </div>

    <form method="POST">
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            Confirmar pedido · R$ <?= number_format($total,2,',','.') ?>
        </button>
    </form>
    <a href="carrinho.php" class="btn btn-secondary btn-block" style="margin-top:10px;text-align:center">← Voltar ao carrinho</a>

<?php endif; ?>
</div>
<?php include 'footer.php'; ?>
