<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$titulo = 'Configurações';
$eId = $_SESSION['usuario_id'];

$estab = $pdo->prepare("SELECT * FROM estabelecimentos WHERE id = ?");
$estab->execute([$eId]);
$estab = $estab->fetch();

$suc = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $cidade   = trim($_POST['cidade'] ?? '');
    $bairro   = trim($_POST['bairro'] ?? '');
    $pix      = trim($_POST['chave_pix'] ?? '');

    if (empty($nome) || empty($cidade)) {
        $erro = 'Nome e cidade são obrigatórios.';
    } elseif (empty($whatsapp)) {
        $erro = 'Informe o número de WhatsApp para contato com os clientes.';
    } else {
        $pdo->prepare("UPDATE estabelecimentos SET nome=?, telefone=?, whatsapp=?, cidade=?, bairro=?, chave_pix=? WHERE id=?")
            ->execute([$nome, $telefone, $whatsapp, $cidade, $bairro, $pix ?: null, $eId]);
        $_SESSION['usuario_nome'] = $nome;
        $suc = 'Configurações salvas com sucesso!';
        // Re-busca dados atualizados
        $s = $pdo->prepare("SELECT * FROM estabelecimentos WHERE id = ?");
        $s->execute([$eId]);
        $estab = $s->fetch();
    }
}

include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1>Configurações</h1>
        <p>Gerencie as informações do seu estabelecimento</p>
    </div>
</div>

<div class="container section" style="max-width:660px;margin-left:auto;margin-right:auto">

    <?php if ($suc): ?><div class="alert alert-success alert-auto"><?= sanitize($suc) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-error"><?= sanitize($erro) ?></div><?php endif; ?>

    <form method="POST">

        <!-- Dados do estabelecimento -->
        <div class="card" style="margin-bottom:18px">
            <div class="card-header"><h3>Dados do estabelecimento</h3></div>
            <div class="card-body-pad">
                <div class="field">
                    <label>Nome do estabelecimento *</label>
                    <input type="text" name="nome" required
                           value="<?= sanitize($_POST['nome'] ?? $estab['nome']) ?>">
                </div>
                <div class="field-row">
                    <div class="field">
                        <label>Cidade *</label>
                        <input type="text" name="cidade" required
                               value="<?= sanitize($_POST['cidade'] ?? $estab['cidade']) ?>">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" name="bairro" placeholder="Opcional"
                               value="<?= sanitize($_POST['bairro'] ?? $estab['bairro']) ?>">
                    </div>
                </div>
                <div class="field-row" style="margin-bottom:0">
                    <div class="field">
                        <label>Telefone</label>
                        <input type="text" name="telefone" placeholder="(11) 99999-9999"
                               value="<?= sanitize($_POST['telefone'] ?? $estab['telefone']) ?>">
                    </div>
                    <div class="field">
                        <label>WhatsApp para contato *</label>
                        <input type="text" name="whatsapp" placeholder="(11) 99999-9999" required
                               value="<?= sanitize($_POST['whatsapp'] ?? $estab['whatsapp'] ?? '') ?>">
                        <div class="field-hint">É o número que aparece no botão de contato dos clientes.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagamento -->
        <div class="card" style="margin-bottom:22px">
            <div class="card-header">
                <div>
                    <h3>💳 Chave Pix</h3>
                    <div class="payment-estab-name">Exibida aos clientes na tela de pagamento</div>
                </div>
                <?php if (!empty($estab['chave_pix'])): ?>
                <span class="badge badge-success">Ativa</span>
                <?php else: ?>
                <span class="badge badge-neutral">Não configurada</span>
                <?php endif; ?>
            </div>
            <div class="card-body-pad">
                <div class="field" style="margin-bottom:10px">
                    <label>Chave Pix</label>
                    <input type="text" name="chave_pix"
                           placeholder="Ex: seuemail@gmail.com, CPF ou chave aleatória"
                           value="<?= sanitize($_POST['chave_pix'] ?? $estab['chave_pix'] ?? '') ?>">
                    <div class="field-hint">
                        Formatos aceitos: e-mail, CPF (000.000.000-00), CNPJ (00.000.000/0001-00), telefone (+55...) ou chave aleatória. Deixe em branco para indicar pagamento na retirada.
                    </div>
                </div>

                <?php if (!empty($estab['chave_pix'])): ?>
                <div style="margin-top:14px">
                    <div style="font-size:.75rem;font-weight:700;color:var(--txt-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
                        Pré-visualização
                    </div>
                    <div class="pix-block">
                        <div class="pix-badge">Pix</div>
                        <div class="pix-key-wrap">
                            <div class="pix-key-label">Chave Pix</div>
                            <div class="pix-key"><?= sanitize($estab['chave_pix']) ?></div>
                        </div>
                        <button type="button" class="btn-copy" data-pix="<?= sanitize($estab['chave_pix']) ?>" onclick="copiarPix(this)">
                            Copiar
                        </button>
                    </div>
                    <p style="font-size:.75rem;color:var(--txt-3);margin-top:8px;margin-bottom:0">
                        Assim aparece para o cliente ao finalizar a compra.
                    </p>
                </div>
                <?php else: ?>
                <div class="pickup-block" style="margin-top:14px">
                    <div class="pickup-ico">💵</div>
                    <div class="pickup-info">
                        <strong>Pagamento na retirada</strong>
                        <span>É isso que o cliente verá enquanto não houver chave Pix.</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">Salvar configurações</button>
    </form>

    <!-- Info somente leitura -->
    <div class="card" style="margin-top:20px">
        <div class="card-header"><h3>🔒 Dados da conta</h3></div>
        <div class="card-body-pad" style="font-size:.875rem;color:var(--txt-2);line-height:2">
            <strong style="color:var(--txt)">E-mail:</strong> <?= sanitize($estab['email']) ?><br>
            <strong style="color:var(--txt)">CNPJ:</strong> <?= $estab['cnpj'] ? sanitize($estab['cnpj']) : '—' ?><br>
            <strong style="color:var(--txt)">Categoria:</strong> <?= categoriaLabel(sanitize($estab['categoria'])) ?>
            <p style="font-size:.78rem;color:var(--txt-3);margin-top:8px;margin-bottom:0">
                Para alterar e-mail, senha ou CNPJ, entre em contato com o suporte.
            </p>
        </div>
    </div>

</div>
<?php include 'footer.php'; ?>
