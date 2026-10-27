<?php
require_once 'conexao.php';
exigeLogin('estabelecimento');

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ? AND estabelecimento_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    if ($stmt->rowCount()) {
        $_SESSION['flash_sucesso'] = 'Produto removido com sucesso.';
    } else {
        $_SESSION['flash_erro'] = 'Produto não encontrado.';
    }
}
redirect('produtos.php');
