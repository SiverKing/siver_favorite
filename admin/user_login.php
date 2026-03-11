<?php
// 读取会话配置（在 session_start 之前设置 cookie 参数）
$sessionCfg = file_exists(__DIR__ . '/session_config.php')
    ? include(__DIR__ . '/session_config.php')
    : ['keep_login_days' => 7, 'session_timeout_minutes' => 0];

$keepDays    = (int)($sessionCfg['keep_login_days'] ?? 7);
$timeoutMins = (int)($sessionCfg['session_timeout_minutes'] ?? 0);

$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

if ($rememberMe && $keepDays > 0) {
    // 记住我：设置 session cookie 有效期
    session_set_cookie_params($keepDays * 86400);
    ini_set('session.gc_maxlifetime', $keepDays * 86400);
} elseif ($timeoutMins > 0) {
    session_set_cookie_params(0); // 浏览器关闭失效
    ini_set('session.gc_maxlifetime', $timeoutMins * 60);
}

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
    exit;
}

$userFile = __DIR__ . '/user.php';
if (!file_exists($userFile)) {
    echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
    exit;
}

$users = include($userFile);

$found = false;
foreach ($users as $user) {
    if ($user['username'] === $username) {
        if (password_verify($password, $user['password'])) {
            $found = true;
        }
        break;
    }
}

if ($found) {
    $_SESSION['loggedin'] = true;
    $_SESSION['user']     = $username;
    $_SESSION['is_admin'] = false;
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
}
?>
