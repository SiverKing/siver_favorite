<?php
require_once __DIR__ . '/session_init.php';
set_session_cookie_params_isolated(0);
session_start();
header('Content-Type: application/json');

$cfg = include(__DIR__ . '/session_config.php');
echo json_encode([
    'status'                  => 'success',
    'keep_login_days'         => (int)($cfg['keep_login_days'] ?? 7),
    'session_timeout_minutes' => (int)($cfg['session_timeout_minutes'] ?? 0),
]);
?>
