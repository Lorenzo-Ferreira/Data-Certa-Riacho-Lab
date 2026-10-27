<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');
$eId = $_SESSION['usuario_id'];

$produto = null; $edit = false;
if (!empty($_GET['id'])) {
    $s = $pdo->prepare("SELECT * FROM produtos WHERE id = ? AND estabelecimento_id = ?");
    $s->execute([$_GET['id'], $eId]);
    $produto = $s->fetch();
    if (!$produto) redirect('produtos.php');
    $edit = true;
}
$titulo = $edit ? 'Editar Produto' : 'Novo Produto';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $desc     = trim($_POST['descricao'] ?? '');
    $cat      = trim($_POST['categoria'] ?? '');
    $qtd      = (int)($_POST['quantidade'] ?? 0);
    $validade = trim($_POST['data_validade'] ?? '');
    $orig     = (float)str_replace(',', '.', $_POST['preco_original'] ?? 0);
    $prom     = (float)str_replace(',', '.', $_POST['preco_promocional'] ?? 0);

    if (empty($nome) || empty($validade) || $orig <= 0 || $prom <= 0) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif ($validade < date('Y-m-d')) {
        $erro = '❌ A data de validade não pode estar no passado.';
    } elseif ($prom >= $orig) {
        $erro = 'O preço promocional deve ser menor que o preço original.';
    } elseif ($qtd <= 0) {
        $erro = 'A quantidade deve ser maior que zero.';
    } else {
        $imagem = $produto['imagem'] ?? null;
        if (!empty($_FILES['imagem']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $erro = 'Formato de imagem inválido.';
                goto fim;
            }
            if ($_FILES['imagem']['size'] > 2 * 1024 * 1024) { $erro = 'Imagem até 2MB.'; goto fim; }
            $fn = uniqid('p_') . '.' . $ext;
            move_uploaded_file($_FILES['imagem']['tmp_name'], __DIR__ . '/uploads/' . $fn);
            $imagem = $fn;
        }
        if ($edit) {
            $pdo->prepare("UPDATE produtos SET nome=?,descricao=?,categoria=?,imagem=?,quantidade=?,data_validade=?,preco_original=?,preco_promocional=?,ativo=1 WHERE id=? AND estabelecimento_id=?")
                ->execute([$nome,$desc,$cat,$imagem,$qtd,$validade,$orig,$prom,$produto['id'],$eId]);
            $_SESSION['flash_sucesso'] = '✅ Produto atualizado.';
        } else {
            $pdo->prepare("INSERT INTO produtos (estabelecimento_id,nome,descricao,categoria,imagem,quantidade,data_validade,preco_original,preco_promocional) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$eId,$nome,$desc,$cat,$imagem,$qtd,$validade,$orig,$prom]);
            $_SESSION['flash_sucesso'] = '✅ Produto cadastrado.';
        }
        redirect('produtos.php');
    }
}
fim:

$cats = ['padaria','hortifruti','mercado','acougue','laticinios','frios','mercearia','fruta','verdura','lanche','bebida','doce','congelado','outros'];
include 'header.php';
?>
<div class="page-header">
    <div class="container">
        <h1><?= $edit ? 'Editar Produto' : 'Novo Produto' ?></h1>
        <p><?= $edit ? 'Atualize as informações do produto' : 'Cadastre um produto com desconto para seus clientes' ?></p>
    </div>
</div>

<div class="container section">
    <div class="form-wrap form-wrap-wide">
        <?php if ($erro): ?>
        <div class="alert alert-error"><?= sanitize($erro) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="field">
                <label>Nome do produto *</label>
                <input type="text" name="nome" placeholder="Ex: Pão Francês"
                       value="<?= sanitize($produto['nome'] ?? ($_POST['nome'] ?? '')) ?>" required>
            </div>
            <div class="field">
                <label>Descrição</label>
                <textarea name="descricao" placeholder="Descreva o produto..."><?= sanitize($produto['descricao'] ?? ($_POST['descricao'] ?? '')) ?></textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Categoria *</label>
                    <select name="categoria" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= $c ?>" <?= ($produto['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= categoriaLabel($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Quantidade *</label>
                    <input type="number" name="quantidade" min="1"
                           value="<?= $produto['quantidade'] ?? ($_POST['quantidade'] ?? '') ?>" required>
                </div>
            </div>
            <div class="field">
                <label>Data de validade * <span style="font-size:.75rem;font-weight:400;color:var(--brand)">(não pode ser no passado)</span></label>
                <input type="date" name="data_validade" min="<?= date('Y-m-d') ?>"
                       value="<?= $produto['data_validade'] ?? ($_POST['data_validade'] ?? '') ?>" required>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Preço original (R$) *</label>
                    <input type="number" name="preco_original" step="0.01" min="0.01" placeholder="0,00"
                           value="<?= $produto['preco_original'] ?? ($_POST['preco_original'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Preço promocional (R$) *</label>
                    <input type="number" name="preco_promocional" step="0.01" min="0.01" placeholder="0,00"
                           value="<?= $produto['preco_promocional'] ?? ($_POST['preco_promocional'] ?? '') ?>" required>
                    <div class="field-hint">Deve ser menor que o preço original</div>
                </div>
            </div>
            <div class="field">
                <label>Foto do produto</label>
                <?php if (!empty($produto['imagem'])): ?>
                <div style="margin-bottom:10px">
                    <img src="uploads/<?= sanitize($produto['imagem']) ?>"
                         style="max-height:120px;border-radius:var(--r2);border:1px solid var(--border)">
                    <p class="field-hint" style="margin-top:6px">Foto atual. Envie uma nova para substituir.</p>
                </div>
                <?php endif; ?>
                <div class="upload-zone" onclick="document.getElementById('img-inp').click()">
                    <div class="upload-ico">📷</div>
                    <p>Clique para enviar uma imagem</p>
                    <p style="font-size:.75rem;color:var(--txt-3)">JPG, PNG, WebP · Até 2MB</p>
                    <img id="img-preview" class="upload-preview" style="display:none" src="" alt="">
                </div>
                <input type="file" id="img-inp" name="imagem" accept="image/*"
                       style="display:none" onchange="previewImagem(this)">
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary btn-lg" style="flex:1">
                    <?= $edit ? '💾 Salvar alterações' : '+ Cadastrar produto' ?>
                </button>
                <a href="produtos.php" class="btn btn-secondary btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
