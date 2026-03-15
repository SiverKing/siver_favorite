<?php
require_once __DIR__ . '/session_init.php';
set_session_cookie_params_isolated(0);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '未授权']);
    exit;
}

$isAdmin = $_SESSION['is_admin'] ?? false;
$user    = $_SESSION['user'] ?? '';

if ($isAdmin) {
    $filePath = __DIR__ . '/../data.json';
} else {
    if (!preg_match('/^[a-zA-Z0-9]{2,16}$/', $user)) {
        echo json_encode(['status' => 'error', 'message' => '非法用户名']);
        exit;
    }
    $filePath = __DIR__ . '/../data/' . $user . '/' . $user . '.json';
}

if (!file_exists($filePath)) {
    // 新用户还没有数据，返回空数组
    echo '[]';
    exit;
}

$content = file_get_contents($filePath);
$parsed  = json_decode($content, true);

if ($parsed === null) {
    echo '[]';
    exit;
}

echo json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
