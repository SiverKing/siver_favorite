# siver_favorite

Siver 的个人网址收藏导航，基于 [jianavi（简约导航开源版）](https://github.com/appexplore/jianavi) 二次开发。

演示地址：https://www.siver.top/favorite_demo

---

## 原项目

本项目基于 [appexplore/jianavi](https://github.com/appexplore/jianavi) 修改而来，jianavi 最初源自小呆导航。感谢原作者的开源贡献。

---

## 相比原版的改动

- **数据与页面分离**：链接数据从 HTML 中抽离，统一存放在 `data.json`，主页动态加载渲染，彻底告别手动编辑 HTML
- **后台管理系统**：新增基于 PHP + 原生 JS 的可视化后台，支持分类和链接的增删改查、拖拽排序，修改后一键保存到服务器
- **多用户系统**：支持管理员 + 普通用户，每个用户拥有独立的收藏数据；前台弹窗登录，无需跳转页面
- **用户管理**：管理员可在后台创建、删除用户，重置密码，查看用户数据
- **登录保持**：支持「记住我」，可配置 Session 有效期
- **自定义背景**：管理员和用户均可在后台设置个性化背景图片，支持上传本地图片或使用在线链接
- **自定义 Favicon**：管理员可在后台自定义网站图标，支持 ICO / PNG / JPG / GIF 上传或在线链接
- **搜索框重写**：仅保留必应 / 谷歌两个引擎切换，修复了原版多处 Bug（Enter 键 crash、搜索联想跨引擎错乱、移动端溢出等），自适应宽度
- **界面优化**：调整了多栏布局、搜索框样式，适配移动端

---

## 文件结构

```
favorite/
├── index.html              # 主页（动态渲染，弹窗登录）
├── data.json               # 公共/管理员链接数据
├── get_favicon.php         # Favicon 代理输出端点（公开）
├── favicon.ico             # 默认 favicon
├── css/
│   ├── style.css           # 主样式
│   └── yidong.css          # 移动端样式
├── js/
│   ├── jquery.js
│   └── js.js               # 页面交互逻辑
├── img/
│   ├── gongan.png
│   └── bg.{ext}            # 管理员上传的背景图片（若有）
├── data/                   # ⚠️ 用户私有数据目录，需配置访问限制（见下方说明）
│   └── {username}/
│       ├── {username}.json # 每个用户的私有收藏数据
│       ├── bg.{ext}        # 用户上传的背景图片（若有）
│       └── bg_config.php   # 用户背景配置
└── admin/
    ├── index.html              # 后台管理界面（管理员+用户共用）
    ├── config.php              # 管理员账号密码配置
    ├── user.php                # 普通用户账号密码存储
    ├── login.php               # 管理员登录接口
    ├── user_login.php          # 普通用户登录接口
    ├── logout.php              # 退出登录接口
    ├── auth_check.php          # 登录状态查询接口
    ├── get_user_data.php       # 获取当前用户数据接口
    ├── update_data.php         # 保存数据接口
    ├── user_manage.php         # 用户管理接口（管理员专用）
    ├── change_password.php     # 修改密码接口
    ├── update_admin.php        # 管理员设置接口
    ├── session_config.php      # Session 保持时长配置
    ├── get_session_config.php  # 获取 Session 配置接口
    ├── update_session_config.php # 更新 Session 配置接口（管理员专用）
    ├── upload_bg.php           # 背景图片上传/设置接口
    ├── get_bg.php              # 背景配置查询 + 图片代理输出接口
    ├── upload_favicon.php      # Favicon 上传/设置接口（管理员专用）
    ├── bg_config.php           # 管理员背景配置（自动生成）
    └── favicon_config.php      # Favicon 配置（自动生成）
```

---

## 部署说明

需要支持 PHP 的虚拟主机或服务器，**无需安装任何 PHP 扩展**。

1. 下载 ZIP，解压后将整个目录上传到服务器
2. 确保服务器对 `data.json`、`data/` 目录、`admin/` 目录、`img/` 目录有**写入权限**
3. `admin/config.php` 默认账号为 `admin`，密码为 `123456`，登录后台后在「设置」中修改
4. 访问 `你的域名/admin/` 登录后台，即可在线管理收藏链接
5. **⚠️ 必须配置 `data/` 目录的访问限制**（见下方安全说明）

---

## ⚠️ 安全说明：保护用户数据

`data/{username}/{username}.json` 存储用户的私有收藏数据，**未经保护时可被任何人直接通过 URL 访问**。

**如何修复：**

### Nginx（推荐）

在站点配置的 `server {}` 块中添加（路径根据实际部署目录调整）：

```nginx
location ~ ^/favorite/data/ {
    deny all;
    return 403;
}
```

宝塔面板操作：网站 → 设置 → 配置文件，在 `#REWRITE-START` 行之前插入上述规则，保存即可。

### Apache

项目已在 `data/` 目录下放置了 `.htaccess` 文件，Apache 服务器会自动生效，无需额外操作。

---

## 使用说明

**前台**：
- 未登录时显示公共链接数据，右上角有「登录」按钮
- 登录后显示个人私有收藏数据，右上角显示用户名、「进入后台」和「退出」
- 搜索框支持必应 / 谷歌切换，选择会记忆在 Cookie 中

**后台**：
- 访问 `/admin/` 或点击前台「进入后台」进入
- **链接管理**：左侧侧边栏管理分类，右侧面板管理链接，支持拖拽排序，修改后点击「保存到服务器」生效
- **用户管理**（管理员专用）：创建/删除用户，重置密码，查看/编辑指定用户的数据
- **设置**：
  - 自定义背景图片（支持上传或在线链接）
  - 修改密码
  - 管理员可额外修改用户名、配置密码加密方式、配置登录保持时长
  - 管理员可自定义网站 Favicon

---

## 版权说明

- 遵循原项目版权要求，请保留页脚的开源地址信息
- 请勿将本项目用于商业出售
