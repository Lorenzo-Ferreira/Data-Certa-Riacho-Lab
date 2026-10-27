<?php
// Escolhe o banco automaticamente: XAMPP quando aberto em localhost,
// InfinityFree quando publicado.
$hostAtual = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]);
if (in_array($hostAtual,['localhost', '127.0.0.1', '::1'])) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'datacerta');
} else {
    define('DB_HOST', 'sql302.infinityfree.com');
    define('DB_USER', 'if0_42548240');
    define('DB_PASS', 'Fsp0t8mAysW3i1');
    define('DB_NAME', 'if0_42548240_datacerta');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Não foi possível conectar ao banco de dados "' . DB_NAME . '". '
      . 'Verifique se o MySQL está ligado no XAMPP e se o database.sql foi importado.');
}

session_start();

// Senhas são guardadas com password_hash (bcrypt), que já vem no PHP.
function gerarHashSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

// Confere a senha digitada com a salva no banco. Contas antigas ainda em MD5
// são aceitas uma última vez e convertidas para bcrypt nesse mesmo login.
function conferirSenha($pdo, $tabela, $usuario, $senha) {
    $hash = $usuario['senha'];
    if (password_verify($senha, $hash)) {
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $pdo->prepare("UPDATE $tabela SET senha = ? WHERE id = ?")->execute([gerarHashSenha($senha), $usuario['id']]);
        }
        return true;
    }
    if (preg_match('/^[a-f0-9]{32}$/', $hash) && hash_equals($hash, md5($senha))) {
        $pdo->prepare("UPDATE $tabela SET senha = ? WHERE id = ?")->execute([gerarHashSenha($senha), $usuario['id']]);
        return true;
    }
    return false;
}

function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

function eTipo($tipo) {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === $tipo;
}

function exigeLogin($tipo = null) {
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
    if ($tipo && !eTipo($tipo)) {
        header('Location: index.php');
        exit;
    }
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Monta uma busca no Google Maps com o endereço completo (rua, número, bairro,
// cidade, estado, CEP e país). Quanto mais completo o endereço, mais precisa
// é a localização que o Google devolve — o CEP em especial ajuda bastante,
// já que no Brasil costuma equivaler a poucos quarteirões (às vezes um só).
function mapsUrl($nome, $rua, $numero, $bairro, $cidade, $estado, $cep = null) {
    $cepFormatado = null;
    if ($cep) {
        $digitos = preg_replace('/\D/', '', $cep);
        if (strlen($digitos) === 8) $cepFormatado = substr($digitos, 0, 5) . '-' . substr($digitos, 5);
    }
    $partes = array_filter([
        $rua . ($numero ? ', ' . $numero : ''),
        $bairro,
        $cidade . ($estado ? ' - ' . $estado : ''),
        $cepFormatado,
        'Brasil',
    ]);
    $q = $nome . ', ' . implode(', ', $partes);
    return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($q);
}

// Mesma cesta ilustrada da home (partials/hero_ilustracao.svg.php), sem a
// etiqueta de desconto e sem o fundo/sombra decorativos do hero — usada como
// logo em header, footer e favicon. $uid evita ids de clip-path duplicados
// quando a marca aparece mais de uma vez na mesma página.
function logoBasketSvg($size = 28, $uid = 'a') {
    $w = round($size * 480 / 420);
    $clip = 'lb-clip-' . $uid;
    return '<svg width="'.$w.'" height="'.$size.'" viewBox="0 0 480 420" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="DataCerta">
      <defs><clipPath id="'.$clip.'"><path d="M120 168 L360 168 L340 372 Q338 388 322 388 L158 388 Q142 388 140 372 Z"/></clipPath></defs>
      <path d="M172 168 Q172 92 240 92 Q308 92 308 168" fill="none" stroke="#FFF3E0" stroke-width="14" stroke-linecap="round"/>
      <path d="M172 168 Q172 92 240 92 Q308 92 308 168" fill="none" stroke="#C62828" stroke-width="6" stroke-linecap="round" opacity=".35"/>
      <path d="M120 168 L360 168 L340 372 Q338 388 322 388 L158 388 Q142 388 140 372 Z" fill="#FFF8F0" stroke="#C62828" stroke-width="6" stroke-linejoin="round"/>
      <path d="M120 168 L360 168 L353 220 L127 220 Z" fill="#E53935"/>
      <g clip-path="url(#'.$clip.')">
        <ellipse cx="205" cy="300" rx="62" ry="40" fill="#E8A452" stroke="#B9772C" stroke-width="4"/>
        <path d="M172 292 Q205 270 238 292" fill="none" stroke="#B9772C" stroke-width="4" stroke-linecap="round"/>
        <path d="M178 306 Q205 288 232 306" fill="none" stroke="#B9772C" stroke-width="4" stroke-linecap="round"/>
        <circle cx="300" cy="312" r="34" fill="#2FAE68" stroke="#1E7E4C" stroke-width="4"/>
        <path d="M300 280 Q296 266 306 258" fill="none" stroke="#6B4226" stroke-width="5" stroke-linecap="round"/>
        <path d="M306 262 Q322 254 330 264" fill="#3FBE78" stroke="#1E7E4C" stroke-width="3"/>
        <path d="M150 340 Q168 300 214 306 Q206 344 168 356 Q152 360 150 340Z" fill="#68C58A" stroke="#2E9A5C" stroke-width="4"/>
      </g>
    </svg>';
}

function faviconHref() {
    return 'data:image/svg+xml;base64,' . base64_encode(logoBasketSvg(64, 'fav'));
}

// Rótulo de categoria com acentuação correta (o valor salvo no banco é
// sempre sem acento, ex: "acougue", "laticinios").
function categoriaLabel($cat) {
    $especiais = ['acougue' => 'Açougue', 'laticinios' => 'Laticínios'];
    return $especiais[$cat] ?? ucfirst($cat);
}

function whatsappUrl($numero, $mensagem = '') {
    $digitos = preg_replace('/\D/', '', (string)$numero);
    if (!$digitos) return '';
    if (strlen($digitos) <= 11) $digitos = '55' . $digitos;
    $url = 'https://wa.me/' . $digitos;
    if ($mensagem !== '') $url .= '?text=' . urlencode($mensagem);
    return $url;
}
