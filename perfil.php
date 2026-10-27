<?php
require_once 'conexao.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$estab = $pdo->prepare("SELECT * FROM estabelecimentos WHERE id = ?");
$estab->execute([$id]);
$estab = $estab->fetch();
if (!$estab) redirect('index.php');
$titulo = sanitize($estab['nome']);

$prods = $pdo->prepare("SELECT *, DATEDIFF(data_validade, CURDATE()) AS dias FROM produtos WHERE estabelecimento_id = ? AND data_validade >= CURDATE() AND quantidade > 0 AND ativo = 1 ORDER BY data_validade");
$prods->execute([$id]);
$prods = $prods->fetchAll();

$s = $pdo->prepare("SELECT ROUND(AVG(nota),1), COUNT(*) FROM avaliacoes WHERE estabelecimento_id = ?");
$s->execute([$id]);
[$media, $totalAv] = $s->fetch(PDO::FETCH_NUM);

$avs = $pdo->prepare("SELECT a.*, c.nome AS cname FROM avaliacoes a JOIN clientes c ON a.cliente_id = c.id WHERE a.estabelecimento_id = ? ORDER BY a.created_at DESC LIMIT 8");
$avs->execute([$id]);
$avs = $avs->fetchAll();

function ec2($d) { if($d<=1) return 'urgent'; if($d<=3) return 'warn'; return 'ok'; }

$mapsUrlEstab = mapsUrl($estab['nome'], $estab['rua'], $estab['numero'], $estab['bairro'], $estab['cidade'], $estab['estado'], $estab['cep']);
$whatsappUrlEstab = whatsappUrl($estab['whatsapp'] ?? '', 'Ola! Vi a loja ' . $estab['nome'] . ' no DataCerta e queria saber mais.');

include 'header.php';
?>
<div class="container" style="padding:32px 24px 60px">

    <div class="store-header">
        <div class="store-ava"><?= mb_strtoupper(mb_substr($estab['nome'],0,1)) ?></div>
        <div class="store-meta" style="flex:1">
            <h2><?= sanitize($estab['nome']) ?></h2>
            <div style="color:var(--txt-2);font-size:.875rem;margin-bottom:8px">
                <?= categoriaLabel(sanitize($estab['categoria'])) ?>
            </div>
            <div class="store-meta-row">
                <?php
                $enderecoPartes = array_filter([
                    $estab['rua'] ? $estab['rua'].($estab['numero']?', '.$estab['numero']:'') : null,
                    $estab['bairro'], $estab['cidade'], $estab['estado']
                ]);
                if (!estaLogado()):
                ?>
                <span class="store-meta-item">
                    <a href="login.php" style="color:var(--brand);font-weight:700;">Entre para ver a localização</a>
                </span>
                <?php elseif ($enderecoPartes): ?>
                <span class="store-meta-item">
                    <?= sanitize(implode(', ', $enderecoPartes)) ?>
                    &nbsp;·&nbsp;
                    <a href="<?= $mapsUrlEstab ?>" target="_blank" rel="noopener"
                       style="color:var(--brand);font-weight:700;">Ver no mapa</a>
                </span>
                <?php endif; ?>
                <?php if (estaLogado() && $estab['telefone']): ?>
                <span class="store-meta-item">Tel: <?= sanitize($estab['telefone']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;min-width:120px">
            <?php if ($media): ?>
            <div style="text-align:center">
                <div style="font-size:2rem;font-weight:800;letter-spacing:-.03em;color:var(--txt)"><?= $media ?></div>
                <div class="stars-display"><?= str_repeat('★',(int)round($media)) ?><?= str_repeat('☆',5-(int)round($media)) ?></div>
                <div style="font-size:.72rem;color:var(--txt-3);margin-top:3px"><?= $totalAv ?> avaliação(ões)</div>
            </div>
            <?php else: ?>
            <div style="font-size:.8rem;color:var(--txt-3)">Sem avaliações</div>
            <?php endif; ?>
            <?php if (estaLogado() && eTipo('cliente') && $whatsappUrlEstab): ?>
            <a href="<?= $whatsappUrlEstab ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm" style="width:100%;justify-content:center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.52 0-10 4.48-10 10 0 1.76.46 3.48 1.34 5L2 22l5.15-1.35A9.96 9.96 0 0012.04 22c5.52 0 10-4.48 10-10s-4.48-10-10-10zm0 18.15a8.15 8.15 0 01-4.15-1.14l-.3-.18-3.06.8.82-2.98-.2-.31a8.12 8.12 0 01-1.25-4.34c0-4.49 3.65-8.14 8.14-8.14 4.49 0 8.14 3.65 8.14 8.14 0 4.49-3.65 8.15-8.14 8.15zm4.48-6.1c-.24-.12-1.43-.7-1.66-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.17-.7-.62-1.18-1.4-1.31-1.64-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.41h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.43-.58 1.63-1.15.2-.56.2-1.04.14-1.15-.06-.1-.22-.16-.46-.28z"/></svg>
                Falar no WhatsApp
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <h2 class="section-title" style="margin-bottom:0">Produtos disponíveis</h2>
        <span style="font-size:.8rem;color:var(--txt-3)"><?= count($prods) ?> produto(s)</span>
    </div>

    <?php if (empty($prods)): ?>
    <div class="card" style="margin-bottom:32px">
        <div class="empty" style="padding:40px 20px">
            <h3>Nenhuma oferta no momento</h3>
        </div>
    </div>
    <?php else: ?>
    <div class="products-grid" style="margin-bottom:40px">
        <?php foreach ($prods as $p):
            $dias = (int)$p['dias'];
            $desc = (int)round((1-$p['preco_promocional']/$p['preco_original'])*100);
            $ec   = ec2($dias);
        ?>
        <article class="product-card" data-cat="<?= sanitize($p['categoria']) ?>" data-nome="<?= sanitize($p['nome']) ?>" onclick="abrirDetalheOferta(<?= $p['id'] ?>)">
            <div class="expiry-strip <?= $ec ?>"></div>
            <div class="card-img-wrap">
                <?php if (!empty($p['imagem']) && file_exists("uploads/".$p['imagem'])): ?>
                <img src="uploads/<?= sanitize($p['imagem']) ?>" alt="">
                <?php else: ?>
                <div class="card-img-placeholder" style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-3)">
                    <?= categoriaLabel($p['categoria']) ?>
                </div>
                <?php endif; ?>
                <span class="discount-chip">-<?= $desc ?>%</span>
            </div>
            <div class="card-body">
                <h3 class="card-name"><?= sanitize($p['nome']) ?></h3>
                <p class="card-desc"><?= sanitize($p['descricao']) ?></p>
                <div class="card-expiry-label <?= $ec ?>">
                    <?= $dias===0?'Vence hoje':($dias===1?'Vence amanhã':"Em {$dias} dias") ?>
                </div>
                <div class="card-footer">
                    <div class="card-price">
                        <div class="price-from">R$ <?= number_format($p['preco_original'],2,',','.') ?></div>
                        <div class="price-now">R$ <?= number_format($p['preco_promocional'],2,',','.') ?></div>
                    </div>
                    <?php if (estaLogado() && eTipo('cliente')): ?>
                    <button class="btn-add" onclick="event.stopPropagation();adicionarCarrinho(<?= $p['id'] ?>, this)">Carrinho</button>
                    <?php elseif (!estaLogado()): ?>
                    <a href="login.php" class="btn-add" onclick="event.stopPropagation()" title="Entre para comprar">Carrinho</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 class="section-title">Avaliações de clientes</h2>
    <?php if (empty($avs)): ?>
    <p style="color:var(--txt-3);font-size:.875rem">Nenhuma avaliação ainda.</p>
    <?php else: ?>
    <?php foreach ($avs as $av): ?>
    <div class="review-card">
        <div class="review-top">
            <div>
                <div class="reviewer"><?= sanitize($av['cname']) ?></div>
                <div class="stars-display sm">
                    <?= str_repeat('★',$av['nota']) ?><?= str_repeat('☆',5-$av['nota']) ?>
                    <span style="font-size:.72rem;color:var(--txt-3);margin-left:4px"><?= $av['nota'] ?>/5</span>
                </div>
            </div>
            <div class="review-date"><?= date('d/m/Y', strtotime($av['created_at'])) ?></div>
        </div>
        <?php if ($av['comentario']): ?>
        <div class="review-text">"<?= sanitize($av['comentario']) ?>"</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
