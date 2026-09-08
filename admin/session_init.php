<?php
// Session 初始化配置
// 用于隔离不同部署实例的 session

// 自动检测项目路径作为 session 隔离标识
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$projectPath = dirname($scriptPath); // 去掉 /admin 部分

// 设置唯一的 session name（基于项目路径）
$sessionName = 'SIVER_FAV_' . md5($projectPath);
session_name($sessionName);

// 辅助函数：获取归一化后的 cookie 路径（避免项目部署在根目录时拼接出非法的 "//" 路径）
function get_isolated_cookie_path() {
    global $projectPath;
    return ($projectPath === '/' || $projectPath === '') ? '/' : $projectPath . '/';
}

// 辅助函数：设置 session cookie 参数（带路径隔离）
function set_session_cookie_params_isolated($lifetime = 0) {
    // 使用关联数组方式（PHP 7.3+），支持 SameSite 属性
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => get_isolated_cookie_path(),
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 读取 session 配置并设置服务端 gc_maxlifetime（防止 session 被过早回收）
$sessionCfg = file_exists(__DIR__ . '/session_config.php')
    ? include(__DIR__ . '/session_config.php')
    : ['keep_login_days' => 7, 'session_timeout_minutes' => 0];

$keepDays    = (int)($sessionCfg['keep_login_days'] ?? 7);
$timeoutMins = (int)($sessionCfg['session_timeout_minutes'] ?? 0);

// 使用配置中的最大值作为 gc_maxlifetime，确保 session 不被过早清理
$maxLifetime = max($keepDays * 86400, $timeoutMins * 60, 1440); // 最小保持 24 分钟
ini_set('session.gc_maxlifetime', $maxLifetime);

// 注意：调用此文件后，外部代码需要自己调用 session_start()
// 并且在 session_start() 之前调用 set_session_cookie_params_isolated() 设置 lifetime
?>
