<?php
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

$filePath = '../data.json';

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
