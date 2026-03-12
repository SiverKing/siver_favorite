<?php
// 公开端点：输出当前 favicon 文件
$cfgFile = __DIR__ . '/admin/favicon_config.php';

if (!file_exists($cfgFile)) {
    // 没有配置，输出默认 favicon.ico
    $default = __DIR__ . '/favicon.ico';
    if (file_exists($default)) {
        header('Content-Type: image/x-icon');
        header('Cache-Control: max-age=3600');
        readfile($default);
    } else {
        http_response_code(404);
    }
    exit;
}

$cfg = include($cfgFile);

if ($cfg['type'] === 'url') {
    // 在线链接：重定向
    header('Location: ' . $cfg['value']);
    exit;
}

if ($cfg['type'] === 'file') {
    $ext  = $cfg['value'];
    $path = __DIR__ . '/favicon.' . $ext;
    if (!file_exists($path)) { http_response_code(404); exit; }
    $mimeMap = [
        'ico'  => 'image/x-icon',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
    ];
    header('Content-Type: ' . ($mimeMap[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: max-age=3600');
    readfile($path);
    exit;
}

// default：输出原始 favicon.ico
$default = __DIR__ . '/favicon.ico';
if (file_exists($default)) {
    header('Content-Type: image/x-icon');
    header('Cache-Control: max-age=3600');
    readfile($default);
} else {
    http_response_code(404);
}
?>
