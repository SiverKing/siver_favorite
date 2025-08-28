<?php
session_start();
header('Content-Type: application/json');

// 调整 config.php 路径（假设 config.php 位于 admin 目录上一级）
$configFile = (__DIR__) . '/config.php';
if (!file_exists($configFile)) {
    echo json_encode(array('status' => 'error', 'message' => '配置文件不存在'));
    exit;
}

$config = include($configFile);

// 使用 isset 判断 POST 参数是否存在
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === $config['username'] && $password === $config['password']) {
    $_SESSION['loggedin'] = true;
    echo json_encode(array('status' => 'success'));
} else {
    echo json_encode(array('status' => 'error', 'message' => '用户名或密码错误'));
}
?>
