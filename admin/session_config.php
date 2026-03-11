<?php
// 登录会话保持时间配置
// keep_login_days: 用户勾选"记住我"后的登录保持天数，0 表示关闭记住我功能
// session_timeout_minutes: 未勾选"记住我"时，session 的最长空闲超时分钟数（0=浏览器关闭即失效）
return [
    'keep_login_days'         => 7,
    'session_timeout_minutes' => 0,
];
?>
