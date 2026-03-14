<?php
require_once __DIR__ . '/session_init.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '无权限']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
    exit;
}

$keepDays    = isset($_POST['keep_login_days']) ? (int)$_POST['keep_login_days'] : 0;
$timeoutMins = isset($_POST['session_timeout_minutes']) ? (int)$_POST['session_timeout_minutes'] : 0;

if ($keepDays < 0 || $keepDays > 365) {
    echo json_encode(['status' => 'error', 'message' => '保持天数范围：0-365 天']);
    exit;
}
if ($timeoutMins < 0 || $timeoutMins > 10080) {
    echo json_encode(['status' => 'error', 'message' => '超时分钟数范围：0-10080（7天）']);
    exit;
}

$cfg = [
    'keep_login_days'         => $keepDays,
    'session_timeout_minutes' => $timeoutMins,
];

$content = "<?php\n// 登录会话保持时间配置\n// keep_login_days: 用户勾选\"记住我\"后的登录保持天数，0 表示关闭记住我功能\n// session_timeout_minutes: 未勾选\"记住我\"时，session 的最长空闲超时分钟数（0=浏览器关闭即失效）\nreturn " . var_export($cfg, true) . ";\n?>";

if (file_put_contents(__DIR__ . '/session_config.php', $content) !== false) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
}
?>
