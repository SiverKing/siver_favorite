<?php
require_once __DIR__ . '/session_init.php';
session_start();
header('Content-Type: application/json');

$loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

echo json_encode([
    'loggedin' => $loggedin,
    'user'     => $loggedin ? ($_SESSION['user'] ?? null) : null,
    'is_admin' => $loggedin ? ($_SESSION['is_admin'] ?? false) : false,
]);
?>
