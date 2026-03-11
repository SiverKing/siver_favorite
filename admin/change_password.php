<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '未授权']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
    exit;
}

$isAdmin        = $_SESSION['is_admin'] ?? false;
$sessionUser    = $_SESSION['user'] ?? '';
$curPassword    = isset($_POST['current_password'])  ? $_POST['current_password']  : '';
$newPassword    = isset($_POST['new_password'])       ? $_POST['new_password']       : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password']   : '';

if ($newPassword !== $confirmPassword) {
    echo json_encode(['status' => 'error', 'message' => '两次输入的新密码不一致']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]{6,16}$/', $newPassword)) {
    echo json_encode(['status' => 'error', 'message' => '密码格式不正确（英文+数字，6-16位）']);
    exit;
}

if ($isAdmin) {
    // 管理员修改自己的密码
    $configFile = __DIR__ . '/config.php';
    $config = include($configFile);

    if (!password_verify($curPassword, $config['password'])) {
        // 兼容明文密码（首次使用未 hash 时）
        $stored = $config['password'];
        $ok = (substr($stored, 0, 4) === '$2y$')
            ? password_verify($curPassword, $stored)
            : ($curPassword === $stored);
        if (!$ok) {
            echo json_encode(['status' => 'error', 'message' => '当前密码错误']);
            exit;
        }
    }

    $config['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    $content = "<?php\nreturn " . var_export($config, true) . ";\n?>";
    if (file_put_contents($configFile, $content) !== false) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
    }
} else {
    // 普通用户修改自己的密码
    $userFile = __DIR__ . '/user.php';
    $users = include($userFile);

    $found = false;
    foreach ($users as &$u) {
        if ($u['username'] === $sessionUser) {
            if (!password_verify($curPassword, $u['password'])) {
                echo json_encode(['status' => 'error', 'message' => '当前密码错误']);
                exit;
            }
            $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $found = true;
            break;
        }
    }
    unset($u);

    if (!$found) {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
        exit;
    }

    $content = "<?php\nreturn " . var_export(array_values($users), true) . ";\n?>";
    if (file_put_contents($userFile, $content) !== false) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
    }
}
?>
