<?php
require_once 'conexao.php';
$titulo = 'Criar Conta';
if (estaLogado()) redirect('index.php');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $senha    = trim($_POST['senha']    ?? '');
    $senha2   = trim($_POST['senha2']   ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cep      = preg_replace('/\D/', '', $_POST['cep']    ?? '');
    $rua      = trim($_POST['rua']      ?? '');
    $numero   = trim($_POST['numero']   ?? '');
    $bairro   = trim($_POST['bairro']   ?? '');
    $cidade   = trim($_POST['cidade']   ?? '');
    $estado   = trim($_POST['estado']   ?? '');
    $lgpd     = !empty($_POST['lgpd']);

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Preencha os campos obrigatórios.';
    } elseif ($senha !== $senha2) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif (!$lgpd) {
        $erro = 'É preciso concordar com o uso dos seus dados para criar a conta.';
    } else {
        $ck = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
        $ck->execute([$email]);
        if ($ck->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $ins = $pdo->prepare("INSERT INTO clientes (nome,email,senha,telefone,cep,rua,numero,bairro,cidade,estado,lgpd_aceite_em) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
            $ins->execute([$nome, $email, gerarHashSenha($senha), $telefone, $cep ?: null, $rua ?: null, $numero ?: null, $bairro ?: null, $cidade ?: null, $estado ?: null]);
            $_SESSION['usuario_id']   = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = 'cliente';
            redirect('index.php');
        }
    }
}
include 'header.php';
?>
<div class="container">
    <div class="form-wrap form-wrap-wide">
        <div class="form-head">
            <h1>Criar sua conta</h1>
            <p>Comece a economizar comprando produtos com desconto</p>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert-error"><?= sanitize($erro) ?></div>
        <?php endif; ?>

        <form method="POST" id="form-cadastro">
            <div class="field">
                <label>Nome completo *</label>
                <input type="text" name="nome" placeholder="Seu nome completo"
                       value="<?= sanitize($_POST['nome'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>E-mail *</label>
                <input type="email" name="email" placeholder="seu@email.com"
                       value="<?= sanitize($_POST['email'] ?? '') ?>" required>
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
            <div class="field">
                <label>Telefone</label>
                <input type="text" name="telefone" placeholder="(11) 99999-9999"
                       value="<?= sanitize($_POST['telefone'] ?? '') ?>">
            </div>

            <p class="section-label" style="margin-top:8px;margin-bottom:14px">Endereço</p>

            <div class="field-row">
                <div class="field">
                    <label>CEP</label>
                    <input type="text" name="cep" id="cep" placeholder="00000-000"
                           maxlength="9" value="<?= sanitize($_POST['cep'] ?? '') ?>"
                           oninput="mascaraCep(this)" onblur="buscarCep(this.value)">
                    <div class="field-hint">Preencha o CEP para auto-completar o endereço.</div>
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
                    <label>Cidade</label>
                    <input type="text" name="cidade" id="cidade" placeholder="Preenchido pelo CEP"
                           value="<?= sanitize($_POST['cidade'] ?? '') ?>">
                </div>
            </div>
            <div class="field" style="max-width:120px">
                <label>Estado</label>
                <input type="text" name="estado" id="estado" placeholder="SP" maxlength="2"
                       value="<?= sanitize($_POST['estado'] ?? '') ?>">
            </div>

            <label class="lgpd-check">
                <input type="checkbox" name="lgpd" value="1" required <?= !empty($_POST['lgpd']) ? 'checked' : '' ?>>
                <span>Li e concordo com o uso dos meus dados conforme a <a href="lgpd.php" target="_blank" rel="noopener">Política de Privacidade e LGPD</a>.</span>
            </label>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                Criar conta gratuitamente
            </button>
        </form>

        <p class="form-link">
            Ja tem conta? <a href="login.php">Entrar</a>
            &nbsp;·&nbsp;
            <a href="cadastro_estabelecimento.php">Sou estabelecimento</a>
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
