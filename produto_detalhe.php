<?php
require_once 'conexao.php';
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Produto inválido.']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, e.nome AS estab_nome, e.cidade AS estab_cidade, e.bairro AS estab_bairro,
           e.rua AS estab_rua, e.numero AS estab_numero, e.estado AS estab_estado,
           e.cep AS estab_cep, e.id AS estab_id, e.whatsapp AS estab_whatsapp,
           DATEDIFF(p.data_validade, CURDATE()) AS dias
    FROM produtos p
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE p.id = ? AND p.data_validade >= CURDATE() AND p.quantidade > 0 AND p.ativo = 1
");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Oferta indisponível.']);
    exit;
}

$jaNoCarrinho = 0;
if (eTipo('cliente')) {
    $c = $pdo->prepare("SELECT quantidade FROM carrinho WHERE cliente_id = ? AND produto_id = ?");
    $c->execute([$_SESSION['usuario_id'], $id]);
    $jaNoCarrinho = (int)$c->fetchColumn();
}

$disponivel = max(0, (int)$p['quantidade'] - $jaNoCarrinho);
$logado = estaLogado();

echo json_encode([
    'sucesso'         => true,
    'id'              => (int)$p['id'],
    'nome'            => $p['nome'],
    'descricao'       => $p['descricao'],
    'categoria'       => $p['categoria'],
    'imagem'          => (!empty($p['imagem']) && file_exists(__DIR__ . '/uploads/' . $p['imagem'])) ? $p['imagem'] : null,
    'estoque'         => (int)$p['quantidade'],
    'disponivel'      => $disponivel,
    'dias'            => (int)$p['dias'],
    'preco_original'  => (float)$p['preco_original'],
    'preco_promocional' => (float)$p['preco_promocional'],
    'estab_id'        => (int)$p['estab_id'],
    'estab_nome'      => $p['estab_nome'],
    // Localização e contato da loja só para quem está logado
    'estab_endereco'  => $logado ? implode(', ', array_filter([$p['estab_bairro'], $p['estab_cidade']])) : '',
    'maps_url'        => $logado ? mapsUrl($p['estab_nome'], $p['estab_rua'], $p['estab_numero'], $p['estab_bairro'], $p['estab_cidade'], $p['estab_estado'], $p['estab_cep']) : '',
    'whatsapp_url'    => $logado ? whatsappUrl($p['estab_whatsapp'] ?? '', 'Olá! Tenho interesse em "' . $p['nome'] . '" pelo DataCerta.') : '',
    'logado'          => $logado,
    'pode_comprar'    => $logado && eTipo('cliente'),
]);
