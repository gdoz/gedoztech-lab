<?php
// Force JSON as response
header('Content-Type: application/json; charset=UTF-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Load config
$config = require __DIR__ . '/../config/config.php';

// Check Origin
$allowedOrigin = rtrim($config['allowed_origin'], '/');
$origin        = $_SERVER['HTTP_ORIGIN']   ?? '';
$referer       = $_SERVER['HTTP_REFERER']  ?? '';

if ($allowedOrigin) {
    $isFromOrigin =
        ($origin && strpos($origin, $allowedOrigin) === 0) ||
        ($referer && strpos($referer, $allowedOrigin) === 0);

    if (!$isFromOrigin) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden origin']);
        exit;
    }
}

// Check Token
$headerToken  = $_SERVER['HTTP_X_VISIT_TOKEN'] ?? '';
$expectedToken = $config['visit_token'] ?? null;

if (!$expectedToken) {
    http_response_code(500);
    echo json_encode(['error' => 'Server misconfigured: missing VISIT_TOKEN']);
    exit;
}

if (!hash_equals($expectedToken, $headerToken)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Reads JSON body
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody ?: '{}', true);
if (!is_array($data)) {
    $data = [];
}

// --- Sanitize page_path ---
$pagePath = isset($data['page']) ? (string) $data['page'] : '/';
$pagePath = trim($pagePath);
if ($pagePath === '') {
    $pagePath = '/';
}
if (strlen($pagePath) > 255) {
    $pagePath = substr($pagePath, 0, 255);
}

// --- Sanitize lang ---
$lang = isset($data['lang']) ? strtolower(trim((string) $data['lang'])) : 'en';
if (!in_array($lang, ['en', 'pt'], true)) {
    $lang = 'en';
}

// --- Sanitize theme ---
$theme = isset($data['theme']) ? strtolower(trim((string) $data['theme'])) : 'auto';
if (!in_array($theme, ['auto', 'light', 'dark'], true)) {
    $theme = 'auto';
}

// --- Sanitize referrer ---
$referrer = isset($data['referrer']) ? trim((string) $data['referrer']) : null;
if ($referrer === '') {
    $referrer = null;
}
if ($referrer !== null && strlen($referrer) > 512) {
    $referrer = substr($referrer, 0, 512);
}

try {
    // Connect to MySQL
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['db']['host'],
        $config['db']['name'],
        $config['db']['charset']
    );
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Insert visit
    $stmt = $pdo->prepare(
        'INSERT INTO visit_log (page_path, lang, theme, referrer)
         VALUES (:page_path, :lang, :theme, :referrer)'
    );
    $stmt->execute([
        ':page_path' => $pagePath,
        ':lang'      => $lang,
        ':theme'     => $theme,
        ':referrer'  => $referrer,
    ]);

    echo json_encode(['status' => 'ok']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    // Log into file
    error_log('[visit.php] ' . $e->getMessage());
}
