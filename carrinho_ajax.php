<?php
require_once 'conexao.php';
header('Content-Type: application/json');

if (!estaLogado() || !eTipo('cliente')) {
    echo json_encode(['sucesso' => false, 'login' => !estaLogado(), 'mensagem' => 'Faça login para adicionar ao carrinho.']);
    exit;
}

$acao = $_POST['acao'] ?? '';
$clienteId = $_SESSION['usuario_id'];

function totalItensCarrinho($pdo, $clienteId) {
    $s = $pdo->prepare("SELECT COALESCE(SUM(quantidade),0) FROM carrinho WHERE cliente_id = ?");
    $s->execute([$clienteId]);
    return (int)$s->fetchColumn();
}

function subtotalCarrinho($pdo, $clienteId) {
    $s = $pdo->prepare("
        SELECT COALESCE(SUM(c.quantidade * p.preco_promocional), 0)
        FROM carrinho c
        JOIN produtos p ON c.produto_id = p.id
        WHERE c.cliente_id = ?
    ");
    $s->execute([$clienteId]);
    return (float)$s->fetchColumn();
}

if ($acao === 'adicionar') {
    $produtoId = (int)($_POST['produto_id'] ?? 0);
    $qtdPedida = (int)($_POST['quantidade'] ?? 1);
    if ($qtdPedida < 1) $qtdPedida = 1;
    if (!$produtoId) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto inválido.']);
        exit;
    }

    // Verificar produto válido
    $stmt = $pdo->prepare("
        SELECT * FROM produtos
        WHERE id = ? AND data_validade >= CURDATE() AND quantidade > 0 AND ativo = 1
    ");
    $stmt->execute([$produtoId]);
    $prod = $stmt->fetch();

    if (!$prod) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto indisponível.']);
        exit;
    }

    // Ver se já está no carrinho
    $existe = $pdo->prepare("SELECT id, quantidade FROM carrinho WHERE cliente_id = ? AND produto_id = ?");
    $existe->execute([$clienteId, $produtoId]);
    $item = $existe->fetch();

    $novaQtd = ($item ? $item['quantidade'] : 0) + $qtdPedida;
    if ($novaQtd > $prod['quantidade']) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Estoque insuficiente. Disponível: ' . $prod['quantidade'] . '.']);
        exit;
    }

    if ($item) {
        $upd = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
        $upd->execute([$novaQtd, $item['id']]);
    } else {
        $ins = $pdo->prepare("INSERT INTO carrinho (cliente_id, produto_id, quantidade) VALUES (?, ?, ?)");
        $ins->execute([$clienteId, $produtoId, $novaQtd]);
    }

    echo json_encode([
        'sucesso' => true,
        'total_itens' => totalItensCarrinho($pdo, $clienteId),
    ]);

} elseif ($acao === 'atualizar') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $qtd    = (int)($_POST['quantidade'] ?? 1);

    if ($qtd < 1) {
        // Remove
        $del = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND cliente_id = ?");
        $del->execute([$itemId, $clienteId]);
    } else {
        // Verificar estoque
        $check = $pdo->prepare("
            SELECT p.quantidade FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.id = ? AND c.cliente_id = ?
        ");
        $check->execute([$itemId, $clienteId]);
        $estoque = (int)$check->fetchColumn();
        if ($qtd > $estoque) $qtd = $estoque;

        $upd = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND cliente_id = ?");
        $upd->execute([$qtd, $itemId, $clienteId]);
    }

    $sub = subtotalCarrinho($pdo, $clienteId);
    echo json_encode([
        'sucesso' => true,
        'total_itens' => totalItensCarrinho($pdo, $clienteId),
        'subtotal' => $sub,
        'total' => $sub,
    ]);

} elseif ($acao === 'remover') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $del = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND cliente_id = ?");
    $del->execute([$itemId, $clienteId]);

    $sub = subtotalCarrinho($pdo, $clienteId);
    echo json_encode([
        'sucesso' => true,
        'total_itens' => totalItensCarrinho($pdo, $clienteId),
        'subtotal' => $sub,
        'total' => $sub,
    ]);

} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
}
