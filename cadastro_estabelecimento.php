<?php
require_once 'conexao.php';
$titulo = 'Cadastrar Estabelecimento';
if (estaLogado()) redirect('index.php');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $senha     = trim($_POST['senha']     ?? '');
    $senha2    = trim($_POST['senha2']    ?? '');
    $cnpj      = trim($_POST['cnpj']      ?? '');
    $telefone  = trim($_POST['telefone']  ?? '');
    $whatsapp  = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    $cep       = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $rua       = trim($_POST['rua']       ?? '');
    $numero    = trim($_POST['numero']    ?? '');
    $bairro    = trim($_POST['bairro']    ?? '');
    $cidade    = trim($_POST['cidade']    ?? '');
    $estado    = trim($_POST['estado']    ?? '');
    $categoria = trim($_POST['categoria'] ?? 'outros');
    $chavePix  = trim($_POST['chave_pix'] ?? '');
    $lgpd      = !empty($_POST['lgpd']);

    if (empty($nome) || empty($email) || empty($senha) || empty($cidade)) {
        $erro = 'Preencha os campos obrigatórios.';
    } elseif ($senha !== $senha2) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif (empty($whatsapp)) {
        $erro = 'Informe o número de WhatsApp para contato com os clientes.';
    } elseif (!$lgpd) {
        $erro = 'É preciso concordar com o uso dos dados para cadastrar o estabelecimento.';
    } else {
        $ck = $pdo->prepare("SELECT id FROM estabelecimentos WHERE email = ?");
        $ck->execute([$email]);
        if ($ck->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $ins = $pdo->prepare("INSERT INTO estabelecimentos (nome,email,senha,cnpj,telefone,whatsapp,cep,rua,numero,bairro,cidade,estado,categoria,chave_pix,lgpd_aceite_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
            $ins->execute([$nome, $email, gerarHashSenha($senha), $cnpj, $telefone, $whatsapp, $cep ?: null, $rua ?: null, $numero ?: null, $bairro ?: null, $cidade, $estado ?: null, $categoria, $chavePix ?: null]);
            $_SESSION['usuario_id']   = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = 'estabelecimento';
            redirect('dashboard.php');
        }
    }
}

$cats = [
    'padaria'    => 'Padaria',
    'hortifruti' => 'Hortifruti',
    'mercado'    => 'Mercado',
    'acougue'    => 'Açougue',
    'laticinios' => 'Laticínios',
    'frios'      => 'Frios',
    'mercearia'  => 'Mercearia',
    'outros'     => 'Outros',
];

include 'header.php';
?>
<div class="container">
    <div class="form-wrap form-wrap-wide">
        <div class="form-head">
            <h1>Cadastrar Estabelecimento</h1>
            <p>Comece a vender produtos próximos da validade com desconto</p>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert-error"><?= sanitize($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Nome do estabelecimento *</label>
                <input type="text" name="nome" placeholder="Ex: Padaria Central"
                       value="<?= sanitize($_POST['nome'] ?? '') ?>" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Categoria *</label>
                    <select name="categoria" required>
                        <?php foreach ($cats as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($_POST['categoria'] ?? '') === $v ? 'selected' : '' ?>>
                            <?= $l ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" placeholder="00.000.000/0001-00"
                           value="<?= sanitize($_POST['cnpj'] ?? '') ?>">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>E-mail *</label>
                    <input type="email" name="email" placeholder="contato@loja.com"
                           value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Telefone</label>
                    <input type="text" name="telefone" placeholder="(11) 99999-9999"
                           value="<?= sanitize($_POST['telefone'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label>WhatsApp para contato *</label>
                <input type="text" name="whatsapp" placeholder="(11) 99999-9999"
                       value="<?= sanitize($_POST['whatsapp'] ?? '') ?>" maxlength="16" required>
                <div class="field-hint">É o número que os clientes usarão para falar com você — substitui o chat.</div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Senha *</label>
                    <input type="password" name="senha" placeholder="Min. 6 caracteres" required>
                </div>
                <div class="field">
                    <label>Confirmar senha *</label>
                    <input type="password" name="senha2" placeholder="Repita a senha" required>
                </div>
            </div>

            <p class="section-label" style="margin-top:8px;margin-bottom:14px">Endereço do estabelecimento</p>

            <div class="field-row">
                <div class="field">
                    <label>CEP</label>
                    <input type="text" name="cep" id="cep" placeholder="00000-000" maxlength="9"
                           value="<?= sanitize($_POST['cep'] ?? '') ?>"
                           oninput="mascaraCep(this)" onblur="buscarCep(this.value)">
                    <div class="field-hint">Auto-completa o endereço.</div>
                </div>
                <div class="field">
                    <label>Número</label>
                    <input type="text" name="numero" id="numero" placeholder="123"
                           value="<?= sanitize($_POST['numero'] ?? '') ?>">
                </div>
            </div>
            <div class="field">
                <label>Rua / Logradouro</label>
                <input type="text" name="rua" id="rua" placeholder="Preenchido pelo CEP"
                       value="<?= sanitize($_POST['rua'] ?? '') ?>">
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Bairro</label>
                    <input type="text" name="bairro" id="bairro" placeholder="Preenchido pelo CEP"
                           value="<?= sanitize($_POST['bairro'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Cidade *</label>
                    <input type="text" name="cidade" id="cidade" placeholder="Preenchido pelo CEP"
                           value="<?= sanitize($_POST['cidade'] ?? '') ?>" required>
                </div>
            </div>
            <div class="field" style="max-width:120px">
                <label>Estado</label>
                <input type="text" name="estado" id="estado" placeholder="SP" maxlength="2"
                       value="<?= sanitize($_POST['estado'] ?? '') ?>">
            </div>

            <div class="field" style="margin-top:8px">
                <label>Chave Pix <span style="font-weight:400;color:var(--txt-3)">(opcional)</span></label>
                <input type="text" name="chave_pix" placeholder="CPF, CNPJ, e-mail, telefone ou chave aleatória"
                       value="<?= sanitize($_POST['chave_pix'] ?? '') ?>">
                <div class="field-hint">Seus clientes verão essa chave ao finalizar a compra.</div>
            </div>

            <label class="lgpd-check">
                <input type="checkbox" name="lgpd" value="1" required <?= !empty($_POST['lgpd']) ? 'checked' : '' ?>>
                <span>Li e concordo com o uso dos dados do meu estabelecimento conforme a <a href="lgpd.php" target="_blank" rel="noopener">Política de Privacidade e LGPD</a>.</span>
            </label>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                Cadastrar estabelecimento
            </button>
        </form>

        <p class="form-link">
            Ja tem conta? <a href="login.php">Entrar</a>
            &nbsp;·&nbsp;
            <a href="cadastro.php">Sou cliente</a>
        </p>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
function mascaraCep(el) {
    let v = el.value.replace(/\D/g, '');
    if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5,8);
    el.value = v;
}

function buscarCep(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length !== 8) return;
    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(r => r.json())
        .then(d => {
            if (d.erro) return;
            document.getElementById('rua').value    = d.logradouro || '';
            document.getElementById('bairro').value = d.bairro     || '';
            document.getElementById('cidade').value = d.localidade || '';
            document.getElementById('estado').value = d.uf         || '';
            document.getElementById('numero').focus();
        })
        .catch(() => {});
}
</script>
