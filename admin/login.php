<?php
require_once __DIR__ . '/session_init.php';

// 读取会话配置（在 session_start 之前设置 cookie 参数）
$sessionCfg = file_exists(__DIR__ . '/session_config.php')
    ? include(__DIR__ . '/session_config.php')
    : ['keep_login_days' => 7, 'session_timeout_minutes' => 0];

$keepDays    = (int)($sessionCfg['keep_login_days'] ?? 7);
$timeoutMins = (int)($sessionCfg['session_timeout_minutes'] ?? 0);

$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

if ($rememberMe && $keepDays > 0) {
    set_session_cookie_params_isolated($keepDays * 86400);
    ini_set('session.gc_maxlifetime', $keepDays * 86400);
} elseif ($timeoutMins > 0) {
    set_session_cookie_params_isolated(0);
    ini_set('session.gc_maxlifetime', $timeoutMins * 60);
} else {
    set_session_cookie_params_isolated(0);
}

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

// 兼容明文密码和 bcrypt hash 两种格式
// 如果 password 以 $2y$ 开头则为 hash，否则为明文
$storedPwd = $config['password'];
if (substr($storedPwd, 0, 4) === '$2y$') {
    $pwdMatch = password_verify($password, $storedPwd);
} else {
    $pwdMatch = ($password === $storedPwd);
}

if ($username === $config['username'] && $pwdMatch) {
    $_SESSION['loggedin'] = true;
    $_SESSION['user'] = 'admin';
    $_SESSION['is_admin'] = true;
    echo json_encode(array('status' => 'success'));
} else {
    echo json_encode(array('status' => 'error', 'message' => '用户名或密码错误'));
}
?>
