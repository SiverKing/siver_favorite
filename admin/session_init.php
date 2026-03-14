<?php
// Session 初始化配置
// 用于隔离不同部署实例的 session

// 自动检测项目路径作为 session 隔离标识
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$projectPath = dirname($scriptPath); // 去掉 /admin 部分

// 设置唯一的 session name（基于项目路径）
$sessionName = 'SIVER_FAV_' . md5($projectPath);
session_name($sessionName);

// 辅助函数：设置 session cookie 参数（带路径隔离）
function set_session_cookie_params_isolated($lifetime = 0) {
    global $projectPath;
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => $projectPath . '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 如果外部没有特殊需求，直接设置默认参数
if (!function_exists('skip_default_cookie_params')) {
    set_session_cookie_params_isolated(0);
}

// 注意：调用此文件后，外部代码需要自己调用 session_start()
// 这样可以让 user_login.php 和 login.php 在 session_start() 前设置 lifetime
?>
