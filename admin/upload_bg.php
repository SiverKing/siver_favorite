<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '未登录']);
    exit;
}

$isAdmin     = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$sessionUser = isset($_SESSION['user']) ? $_SESSION['user'] : '';

// 用户名合法性验证（非管理员）
if (!$isAdmin && !preg_match('/^[a-zA-Z0-9]{5,16}$/', $sessionUser)) {
    echo json_encode(['status' => 'error', 'message' => '非法用户名']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : 'upload';

// ─── 保存在线链接 ────────────────────────────────────────────────────────────
if ($action === 'set_url') {
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';
    if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'error', 'message' => '链接格式不正确']);
        exit;
    }
    saveBgConfig($isAdmin, $sessionUser, 'url', $url);
    echo json_encode(['status' => 'success', 'bg' => $url]);
    exit;
}

// ─── 清除背景 ────────────────────────────────────────────────────────────────
if ($action === 'clear') {
    saveBgConfig($isAdmin, $sessionUser, 'url', '');
    echo json_encode(['status' => 'success', 'bg' => '']);
    exit;
}

// ─── 上传文件 ────────────────────────────────────────────────────────────────
if (!isset($_FILES['bg_file']) || $_FILES['bg_file']['error'] !== UPLOAD_ERR_OK) {
    $code = isset($_FILES['bg_file']) ? $_FILES['bg_file']['error'] : -1;
    $msg  = $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE
          ? '文件超过大小限制（最大 10MB）'
          : '上传失败，请重试';
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

$file = $_FILES['bg_file'];

// 大小限制：10MB
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => '文件不能超过 10MB']);
    exit;
}

// 通过文件头 magic bytes 检测真实类型（无需 fileinfo 扩展）
$handle = fopen($file['tmp_name'], 'rb');
$header = fread($handle, 12);
fclose($handle);

if (substr($header, 0, 2) === "\xFF\xD8") {
    $ext = 'jpg';
} elseif (substr($header, 0, 8) === "\x89PNG\r\n\x1A\n") {
    $ext = 'png';
} elseif (substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
    $ext = 'webp';
} else {
    echo json_encode(['status' => 'error', 'message' => '只允许上传 JPG、PNG、WEBP 格式的图片']);
    exit;
}

if ($isAdmin) {
    // 管理员：保存到 img/bg.{ext}，先删旧文件
    $saveDir = __DIR__ . '/../img/';
    foreach (['jpg','png','webp'] as $e) {
        $old = $saveDir . 'bg.' . $e;
        if (file_exists($old)) unlink($old);
    }
    $savePath = $saveDir . 'bg.' . $ext;
    $webPath  = '../img/bg.' . $ext;
} else {
    // 普通用户：保存到 data/{user}/bg.{ext}
    $userDir = __DIR__ . '/../data/' . $sessionUser . '/';
    if (!is_dir($userDir)) mkdir($userDir, 0755, true);
    foreach (['jpg','png','webp'] as $e) {
        $old = $userDir . 'bg.' . $e;
        if (file_exists($old)) unlink($old);
    }
    $savePath = $userDir . 'bg.' . $ext;
    $webPath  = 'bg_image/' . $sessionUser;   // 指向代理端点
}

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['status' => 'error', 'message' => '保存失败，请检查服务器权限']);
    exit;
}

// 保存配置：类型=file
saveBgConfig($isAdmin, $sessionUser, 'file', $ext);

// 返回可访问的 URL（使用绝对路径，前台后台均可直接使用）
$bgScript = str_replace('upload_bg.php', 'get_bg.php', $_SERVER['SCRIPT_NAME']);
$preview  = $bgScript . '?type=file&_t=' . time();
echo json_encode(['status' => 'success', 'bg' => $preview]);

// ─── 写入配置 ────────────────────────────────────────────────────────────────
function saveBgConfig($isAdmin, $user, $type, $value) {
    if ($isAdmin) {
        $cfgFile = __DIR__ . '/bg_config.php';
    } else {
        $dir = __DIR__ . '/../data/' . $user . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $cfgFile = $dir . 'bg_config.php';
    }
    $data    = ['type' => $type, 'value' => $value];
    $content = "<?php\nreturn " . var_export($data, true) . ";\n?>";
    file_put_contents($cfgFile, $content);
}
?>
