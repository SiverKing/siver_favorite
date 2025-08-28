<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '未授权']);
    exit;
}

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newLinksHtml = isset($_POST['links']) ? $_POST['links'] : '';
    $filePath = '../index.html'; // 根据实际情况调整路径

    if (!file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'index.html 文件不存在']);
        exit;
    }

    // 读取原 index.html 内容，假设用 <!-- START_LINKS --> 和 <!-- END_LINKS --> 标识链接区域
    $fileContent = file_get_contents($filePath);
    $pattern = '/(<!--\s*START_LINKS\s*-->)(.*?)(<!--\s*END_LINKS\s*-->)/s';
    if (preg_match($pattern, $fileContent)) {
        $newContent = preg_replace($pattern, "$1\n" . $newLinksHtml . "\n$3", $fileContent);
        if (file_put_contents($filePath, $newContent)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '写入文件失败']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '未找到链接数据标识']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
}
?>
