<?php
session_start();
header('Content-Type: application/json');

// 仅管理员可访问
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '无权限']);
    exit;
}

$action  = isset($_POST['action']) ? $_POST['action'] : 'upload';
$cfgFile = __DIR__ . '/favicon_config.php';

function saveFaviconConfig($type, $value) {
    global $cfgFile;
    $content = "<?php\nreturn " . var_export(['type' => $type, 'value' => $value], true) . ";\n?>";
    file_put_contents($cfgFile, $content);
}

// ─── 保存在线链接 ────────────────────────────────────────────────────────────
if ($action === 'set_url') {
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';
    if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'error', 'message' => '链接格式不正确']);
        exit;
    }
    saveFaviconConfig('url', $url);
    echo json_encode(['status' => 'success', 'favicon' => $url]);
    exit;
}

// ─── 重置为默认 ──────────────────────────────────────────────────────────────
if ($action === 'reset') {
    saveFaviconConfig('default', '');
    echo json_encode(['status' => 'success', 'favicon' => '']);
    exit;
}

// ─── 上传文件 ────────────────────────────────────────────────────────────────
if (!isset($_FILES['favicon_file']) || $_FILES['favicon_file']['error'] !== UPLOAD_ERR_OK) {
    $code = isset($_FILES['favicon_file']) ? $_FILES['favicon_file']['error'] : -1;
    $msg  = $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE
          ? '文件超过大小限制'
          : '上传失败，请重试';
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

$file = $_FILES['favicon_file'];

// 大小限制：2MB
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => '文件不能超过 2MB']);
    exit;
}

// 通过文件头 magic bytes 检测类型
$handle = fopen($file['tmp_name'], 'rb');
$header = fread($handle, 16);
fclose($handle);

if (substr($header, 0, 4) === "\x00\x00\x01\x00" || substr($header, 0, 4) === "\x00\x00\x02\x00") {
    $ext = 'ico';
} elseif (substr($header, 0, 8) === "\x89PNG\r\n\x1A\n") {
    $ext = 'png';
} elseif (substr($header, 0, 2) === "\xFF\xD8") {
    $ext = 'jpg';
} elseif (substr($header, 0, 6) === 'GIF87a' || substr($header, 0, 6) === 'GIF89a') {
    $ext = 'gif';
} else {
    echo json_encode(['status' => 'error', 'message' => '只允许上传 ICO、PNG、JPG、GIF 格式的图片']);
    exit;
}

$rootDir = __DIR__ . '/../';

// 删除旧的 favicon 文件（所有支持的扩展名）
foreach (['ico', 'png', 'jpg', 'jpeg', 'gif'] as $e) {
    $old = $rootDir . 'favicon.' . $e;
    if (file_exists($old)) unlink($old);
}

$savePath = $rootDir . 'favicon.' . $ext;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['status' => 'error', 'message' => '保存失败，请检查服务器权限']);
    exit;
}

// 保存配置
$faviconUrl = str_replace('upload_favicon.php', '../get_favicon.php', $_SERVER['SCRIPT_NAME']);
saveFaviconConfig('file', $ext);

echo json_encode(['status' => 'success', 'favicon' => $faviconUrl . '?_t=' . time()]);
?>
