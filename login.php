<?php
require_once 'conexao.php';
$titulo = 'Entrar';
if (estaLogado()) redirect('index.php');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $tipo  = $_POST['tipo_usuario'] ?? 'cliente';
    $lgpd  = !empty($_POST['lgpd']);

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha e-mail e senha.';
    } elseif (!$lgpd) {
        $erro = 'É preciso concordar com o uso dos seus dados para entrar.';
    } else {
        $tabela = $tipo === 'estabelecimento' ? 'estabelecimentos' : 'clientes';
        $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && conferirSenha($pdo, $tabela, $user, $senha)) {
            $_SESSION['usuario_id']   = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_tipo'] = $tipo;
            redirect($tipo === 'estabelecimento' ? 'dashboard.php' : 'index.php');
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
include 'header.php';
?>
<div class="container">
    <div class="form-wrap">
        <div class="form-head">
            <h1>Bem-vindo de volta</h1>
            <p>Acesse sua conta DataCerta</p>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert-error"><?= sanitize($erro) ?></div>
        <?php endif; ?>

        <div class="type-tabs">
            <button class="type-tab active" data-tipo="cliente">Cliente</button>
            <button class="type-tab" data-tipo="estabelecimento">Estabelecimento</button>
        </div>

        <form method="POST">
            <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="cliente">
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="seu@email.com"
                       value="<?= sanitize($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
            </div>

            <label class="lgpd-check">
                <input type="checkbox" name="lgpd" value="1" required>
                <span>Concordo com o uso dos meus dados conforme a <a href="lgpd.php" target="_blank" rel="noopener">Política de Privacidade e LGPD</a>.</span>
            </label>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:6px">
                Entrar
            </button>
        </form>

        <div class="form-divider"><span>ou</span></div>

        <p class="form-link">
            Não tem conta?
            <a href="cadastro.php">Criar conta de cliente</a>
            &nbsp;·&nbsp;
            <a href="cadastro_estabelecimento.php">Sou estabelecimento</a>
        </p>

        <div class="demo-box">
            <strong>Contas demo:</strong><br>
            Cliente: joao@demo.com / 123456<br>
            Estabelecimento: padaria@demo.com / 123456
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
document.querySelector('.type-tabs').addEventListener('click', e => {
    const btn = e.target.closest('.type-tab');
    if (!btn) return;
    document.querySelectorAll('.type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tipo_usuario').value = btn.dataset.tipo;
});
</script>
