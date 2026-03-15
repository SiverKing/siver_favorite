<?php
require_once __DIR__ . '/session_init.php';
set_session_cookie_params_isolated(0);
session_start();
header('Content-Type: application/json');

session_unset();
session_destroy();

echo json_encode(['status' => 'success']);
?>
