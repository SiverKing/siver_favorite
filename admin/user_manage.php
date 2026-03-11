<?php
session_start();
header('Content-Type: application/json');

// 仅管理员可访问
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

$action   = isset($_POST['action']) ? $_POST['action'] : '';
$userFile = __DIR__ . '/user.php';

// 辅助：从 user.php 读取用户数组
function loadUsers($file) {
    if (!file_exists($file)) return [];
    return include($file);
}

// 辅助：将用户数组写回 user.php
function saveUsers($file, $users) {
    $content = "<?php\nreturn " . var_export(array_values($users), true) . ";\n?>";
    return file_put_contents($file, $content) !== false;
}

// 辅助：递归删除目录
function deleteDir($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDir($path) : unlink($path);
    }
    rmdir($dir);
}

switch ($action) {

    case 'list':
        $users = loadUsers($userFile);
        $usernames = array_column($users, 'username');
        echo json_encode(['status' => 'success', 'users' => $usernames]);
        break;

    case 'add':
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (!preg_match('/^[a-zA-Z0-9]{5,16}$/', $username)) {
            echo json_encode(['status' => 'error', 'message' => '用户名格式不正确（英文+数字，5-16位）']);
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9]{6,16}$/', $password)) {
            echo json_encode(['status' => 'error', 'message' => '密码格式不正确（英文+数字，6-16位）']);
            exit;
        }

        $users = loadUsers($userFile);
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                echo json_encode(['status' => 'error', 'message' => '用户名已存在']);
                exit;
            }
        }

        $users[] = ['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)];
        if (saveUsers($userFile, $users)) {
            // 创建用户数据目录，写入硬编码的默认初始数据
            $userDataDir  = __DIR__ . '/../data/' . $username;
            $userDataFile = $userDataDir . '/' . $username . '.json';
            if (!is_dir($userDataDir)) {
                mkdir($userDataDir, 0755, true);
            }
            if (!file_exists($userDataFile)) {
                $defaultData = '[
    {
        "category": "学习 · 生活",
        "links": [
            {"name": "淘宝", "url": "https://www.taobao.com/"},
            {"name": "京东", "url": "https://www.jd.com/"},
            {"name": "盖得排行", "url": "https://guiderank-app.com/"},
            {"name": "银行", "url": "http://www.hao123.com/bank"},
            {"name": "地图", "url": "https://www.amap.com/"},
            {"name": "翻译", "url": "https://translate.google.cn/"},
            {"name": "邮箱", "url": "http://www.hao123.com/mail"},
            {"name": "菜鸟教程", "url": "https://www.runoob.com/"},
            {"name": "MOOC", "url": "https://www.icourse163.org/"}
        ]
    },
    {
        "category": "常用 · 社区",
        "links": [
            {"name": "微信", "url": "https://wx.qq.com/"},
            {"name": "微博", "url": "https://weibo.com/"},
            {"name": "知乎", "url": "https://www.zhihu.com/"},
            {"name": "贴吧", "url": "https://tieba.baidu.com/"},
            {"name": "Soomal", "url": "http://www.soomal.com/"},
            {"name": "Topbook", "url": "https://topbook.cc/overview"},
            {"name": "GitHub", "url": "https://github.com/"},
            {"name": "豆瓣", "url": "https://www.douban.com/"},
            {"name": "V2EX", "url": "https://www.v2ex.com/"}
        ]
    },
    {
        "category": "影音 · 娱乐",
        "links": [
            {"name": "爱奇艺", "url": "https://www.iqiyi.com/"},
            {"name": "腾讯视频", "url": "https://v.qq.com/"},
            {"name": "哔哩哔哩", "url": "https://www.bilibili.com/"},
            {"name": "芒果TV", "url": "https://www.mgtv.com/"},
            {"name": "优酷", "url": "https://www.youku.com/"},
            {"name": "音乐", "url": "https://music.163.com/"},
            {"name": "电影FM", "url": "https://dianying.fm/"},
            {"name": "低端影视", "url": "https://ddrk.me/"},
            {"name": "ZzzFun", "url": "http://www.zzzfun.com/"}
        ]
    },
    {
        "category": "发现 · 世界",
        "links": [
            {"name": "凤凰资讯", "url": "https://news.ifeng.com/"},
            {"name": "知微事见", "url": "https://ef.zhiweidata.com/#!/index"},
            {"name": "少数派", "url": "https://sspai.com/"},
            {"name": "小鸡词典", "url": "https://jikipedia.com/"},
            {"name": "煎蛋", "url": "https://jandan.net/"},
            {"name": "有趣网址", "url": "https://youquhome.com/"},
            {"name": "聚合AI", "url": "http://www.inoneai.com/"},
            {"name": "后续", "url": "https://houxu.app/"},
            {"name": "思谋学术", "url": "https://ac.scmor.com/"}
        ]
    },
    {
        "category": "在线 · 工具",
        "links": [
            {"name": "在线修图", "url": "https://www.photopea.com/"},
            {"name": "收发文件", "url": "https://cowtransfer.com/"},
            {"name": "二维码", "url": "https://cli.im/"},
            {"name": "临时邮箱", "url": "https://www.linshiyouxiang.net"},
            {"name": "临时短信", "url": "https://www.materialtools.com/"},
            {"name": "格式转换", "url": "https://convertio.co/zh/"},
            {"name": "文档下载", "url": "http://www.hiwenku.com/"},
            {"name": "视频下载", "url": "https://weibomiaopai.com/"},
            {"name": "音乐下载", "url": "https://www.eggvod.cn/music/"}
        ]
    },
    {
        "category": "搜索 · 资源",
        "links": [
            {"name": "Siver主站", "url": "https://www.siver.top/"},
            {"name": "电影天堂", "url": "https://www.dy2018.com/"},
            {"name": "酷软清单", "url": "https://www.coolist.net/"},
            {"name": "知晓程序", "url": "https://minapp.com/"},
            {"name": "简约导航", "url": "https://www.jianavi.com/"},
            {"name": "搜酷站", "url": "https://www.soukuzhan.com/"},
            {"name": "电子书", "url": "https://www.jiumodiary.com/"},
            {"name": "常用软件", "url": "https://cloud.coolist.net/"},
            {"name": "字幕库", "url": "http://www.zimuku.la/"}
        ]
    }
]';
                file_put_contents($userDataFile, $defaultData);
            }
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
        }
        break;

    case 'delete':
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        if (!preg_match('/^[a-zA-Z0-9]{5,16}$/', $username)) {
            echo json_encode(['status' => 'error', 'message' => '非法用户名']);
            exit;
        }

        $users = loadUsers($userFile);
        $newUsers = array_filter($users, function($u) use ($username) {
            return $u['username'] !== $username;
        });

        if (count($newUsers) === count($users)) {
            echo json_encode(['status' => 'error', 'message' => '用户不存在']);
            exit;
        }

        // 删除用户数据目录
        $dataDir = __DIR__ . '/../data/' . $username;
        deleteDir($dataDir);

        if (saveUsers($userFile, $newUsers)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
        }
        break;

    case 'reset_password':
        $username    = isset($_POST['username']) ? trim($_POST['username']) : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';

        if (!preg_match('/^[a-zA-Z0-9]{5,16}$/', $username)) {
            echo json_encode(['status' => 'error', 'message' => '非法用户名']);
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9]{6,16}$/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => '密码格式不正确（英文+数字，6-16位）']);
            exit;
        }

        $users = loadUsers($userFile);
        $found = false;
        foreach ($users as &$u) {
            if ($u['username'] === $username) {
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

        if (saveUsers($userFile, $users)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '保存失败，请检查文件权限']);
        }
        break;

    case 'view_data':
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        if (!preg_match('/^[a-zA-Z0-9]{5,16}$/', $username)) {
            echo json_encode(['status' => 'error', 'message' => '非法用户名']);
            exit;
        }

        $filePath = __DIR__ . '/../data/' . $username . '/' . $username . '.json';
        if (!file_exists($filePath)) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        $content = file_get_contents($filePath);
        $data    = json_decode($content, true);
        echo json_encode(['status' => 'success', 'data' => $data ?? []]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => '未知操作']);
        break;
}
?>
