<?php
require_once __DIR__ . '/session_init.php';
set_session_cookie_params_isolated(0);
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '未授权']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
    exit;
}

$rawData = isset($_POST['data']) ? $_POST['data'] : '';
if (empty($rawData)) {
    echo json_encode(['status' => 'error', 'message' => '数据为空']);
    exit;
}

// 验证JSON格式
$parsed = json_decode($rawData, true);
if ($parsed === null) {
    echo json_encode(['status' => 'error', 'message' => 'JSON格式错误: ' . json_last_error_msg()]);
    exit;
}

// 根据身份决定写入哪个文件
$isAdmin = $_SESSION['is_admin'] ?? false;
$sessionUser = $_SESSION['user'] ?? '';

if ($isAdmin) {
    // 管理员代用户保存
    if (isset($_POST['target_user']) && preg_match('/^[a-zA-Z0-9]{2,16}$/', $_POST['target_user'])) {
        $targetUser = $_POST['target_user'];
        $dir = __DIR__ . '/../data/' . $targetUser;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filePath = $dir . '/' . $targetUser . '.json';
    } else {
        $filePath = __DIR__ . '/../data.json';
    }
} else {
    // 普通用户保存自己的数据
    if (!preg_match('/^[a-zA-Z0-9]{2,16}$/', $sessionUser)) {
        echo json_encode(['status' => 'error', 'message' => '非法用户名']);
        exit;
    }
    $dir = __DIR__ . '/../data/' . $sessionUser;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $filePath = $dir . '/' . $sessionUser . '.json';
}

// 备份原文件
if (file_exists($filePath)) {
    copy($filePath, $filePath . '.bak');
}

// 写入格式化的JSON
$formatted = json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($filePath, $formatted) !== false) {
    echo json_encode(['status' => 'success']);
} else {
    // 恢复备份
    if (file_exists($filePath . '.bak')) {
        copy($filePath . '.bak', $filePath);
    }
    echo json_encode(['status' => 'error', 'message' => '写入文件失败，请检查目录权限']);
}
?>
