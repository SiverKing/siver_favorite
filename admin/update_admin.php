<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '无权限']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '非法请求']);
    exit;
}

$action     = isset($_POST['action']) ? $_POST['action'] : '';
$configFile = __DIR__ . '/config.php';
$config     = include($configFile);

function saveConfig($file, $config) {
    $content = "<?php\nreturn " . var_export($config, true) . ";\n?>";
    return file_put_contents($file, $content) !== false;
}

// 判断当前密码是否已 hash
function isHashed($pwd) {
    return substr($pwd, 0, 4) === '$2y$';
}

switch ($action) {

    // 修改管理员用户名
    case 'change_username':
        $newUsername = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';
        $curPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';

        if (empty($newUsername)) {
            echo json_encode(['status' => 'error', 'message' => '用户名不能为空']);
            exit;
        }
        if (strlen($newUsername) < 2 || strlen($newUsername) > 32) {
            echo json_encode(['status' => 'error', 'message' => '用户名长度应为 2-32 位']);
            exit;
        }

        // 验证当前密码
        $stored = $config['password'];
        $ok = isHashed($stored)
            ? password_verify($curPassword, $stored)
            : ($curPassword === $stored);
        if (!$ok) {
            echo json_encode(['status' => 'error', 'message' => '当前密码错误，无法修改用户名']);
            exit;
        }

        $config['username'] = $newUsername;
        if (saveConfig($configFile, $config)) {
            // 更新 session
            $_SESSION['user'] = $newUsername;
            echo json_encode(['status' => 'success', 'new_username' => $newUsername]);
        } else {
            echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
        }
        break;

    // 切换密码 hash 模式（明文 ↔ hash）
    case 'toggle_hash':
        $curPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $enableHash  = isset($_POST['enable_hash']) ? ($_POST['enable_hash'] === '1') : true;

        // 验证当前密码（兼容两种格式）
        $stored = $config['password'];
        $ok = isHashed($stored)
            ? password_verify($curPassword, $stored)
            : ($curPassword === $stored);
        if (!$ok) {
            echo json_encode(['status' => 'error', 'message' => '当前密码错误']);
            exit;
        }

        if ($enableHash) {
            // 明文 → hash
            if (isHashed($stored)) {
                echo json_encode(['status' => 'error', 'message' => '密码已经是 hash 格式']);
                exit;
            }
            $config['password'] = password_hash($stored, PASSWORD_DEFAULT);
        } else {
            // hash → 明文（需要提供明文密码）
            if (!isHashed($stored)) {
                echo json_encode(['status' => 'error', 'message' => '密码已经是明文格式']);
                exit;
            }
            // 关闭 hash 时，用提交的明文密码存储
            $config['password'] = $curPassword;
        }

        if (saveConfig($configFile, $config)) {
            echo json_encode(['status' => 'success', 'hashed' => $enableHash]);
        } else {
            echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
        }
        break;

    // 查询当前 hash 状态
    case 'get_status':
        echo json_encode([
            'status'   => 'success',
            'username' => $config['username'],
            'hashed'   => isHashed($config['password']),
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => '未知操作']);
        break;
}
?>
