<?php
session_start();

$isLoggedin  = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$isAdmin     = $isLoggedin && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$sessionUser = $isLoggedin ? (isset($_SESSION['user']) ? $_SESSION['user'] : '') : '';

// 构建当前脚本的绝对 URL 路径（不含域名，如 /favorite_demo/admin/get_bg.php）
$scriptUrl = $_SERVER['SCRIPT_NAME'];
$proxyUrl  = $scriptUrl . '?type=file&_t=' . time();

// ─── 代理输出背景图片 ────────────────────────────────────────────────────────
// ?type=file  → 直接输出图片二进制
if (isset($_GET['type']) && $_GET['type'] === 'file') {
    if ($isAdmin) {
        $dir = __DIR__ . '/../img/';
    } elseif ($isLoggedin && preg_match('/^[a-zA-Z0-9]{2,16}$/', $sessionUser)) {
        $dir = __DIR__ . '/../data/' . $sessionUser . '/';
    } else {
        // 未登录时也可访问管理员背景图片（公开展示用）
        $dir = __DIR__ . '/../img/';
    }

    $found = null;
    foreach (['jpg','png','webp'] as $e) {
        $p = $dir . 'bg.' . $e;
        if (file_exists($p)) { $found = ['path' => $p, 'ext' => $e]; break; }
    }

    if (!$found) { http_response_code(404); exit; }

    $mimeMap = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    header('Content-Type: ' . $mimeMap[$found['ext']]);
    header('Cache-Control: no-cache');
    readfile($found['path']);
    exit;
}

// ─── 返回背景配置 JSON ───────────────────────────────────────────────────────
header('Content-Type: application/json');

function loadBgConfig($isAdmin, $user) {
    if ($isAdmin) {
        $cfgFile = __DIR__ . '/bg_config.php';
    } else {
        if (!preg_match('/^[a-zA-Z0-9]{2,16}$/', $user)) return null;
        $cfgFile = __DIR__ . '/../data/' . $user . '/bg_config.php';
    }
    if (!file_exists($cfgFile)) return null;
    return include($cfgFile);
}

// 未登录 → 返回管理员背景（前台未登录时显示）
if (!$isLoggedin) {
    $cfg = loadBgConfig(true, '');
    if (!$cfg || $cfg['value'] === '') {
        echo json_encode(['bg' => '']);
    } elseif ($cfg['type'] === 'url') {
        echo json_encode(['bg' => $cfg['value']]);
    } else {
        echo json_encode(['bg' => $proxyUrl]);
    }
    exit;
}

// 已登录管理员 → 管理员背景
if ($isAdmin) {
    $cfg = loadBgConfig(true, '');
    if (!$cfg || $cfg['value'] === '') {
        echo json_encode(['bg' => '']);
    } elseif ($cfg['type'] === 'url') {
        echo json_encode(['bg' => $cfg['value']]);
    } else {
        echo json_encode(['bg' => $proxyUrl]);
    }
    exit;
}

// 普通用户 → 用户自己的背景
if (!preg_match('/^[a-zA-Z0-9]{2,16}$/', $sessionUser)) {
    echo json_encode(['bg' => '']);
    exit;
}

$cfg = loadBgConfig(false, $sessionUser);
if (!$cfg || $cfg['value'] === '') {
    echo json_encode(['bg' => '']);
} elseif ($cfg['type'] === 'url') {
    echo json_encode(['bg' => $cfg['value']]);
} else {
    echo json_encode(['bg' => $proxyUrl]);
}
?>
