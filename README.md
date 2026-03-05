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
- **搜索框重写**：仅保留必应 / 谷歌两个引擎切换，修复了V1.0多处 Bug（Enter 键 crash、搜索联想跨引擎错乱、移动端溢出等），自适应宽度
- **界面优化**：调整了多栏布局、搜索框样式，适配移动端

---

## 文件结构

```
favorite/
├── index.html          # 主页（从 data.json 动态渲染）
├── data.json           # 链接数据文件（所有分类和链接存这里）
├── favicon.ico
├── css/
│   ├── style.css       # 主样式
│   └── yidong.css      # 移动端样式
├── js/
│   ├── jquery.js
│   └── js.js           # 页面交互逻辑
├── img/
│   └── gongan.png
└── admin/
    ├── index.html      # 后台管理界面
    ├── login.php       # 登录验证
    ├── config.php      # 账号密码配置
    └── save_data.php   # 保存 data.json 的后端接口
```

---

## 部署说明

需要支持 PHP 的虚拟主机或服务器（用于后台保存功能）。

1. 下载 ZIP，解压后将整个 `favorite/` 目录上传到服务器根目录
2. 确保服务器对 `data.json` 有**写入权限**
3. 修改 `admin/config.php` 设置你自己的后台账号密码：

```php
<?php
return [
    'username' => '你的用户名',
    'password' => '你的密码'
];
?>
```

4. 访问 `你的域名/admin/` 登录后台，即可在线管理收藏链接

---

## 使用说明

**前台**：直接访问网站，搜索框支持必应 / 谷歌切换，选择会记忆在 Cookie 中。

**后台**：
- 访问 `/admin/` 登录
- 左侧侧边栏管理分类（可添加、重命名、删除）
- 右侧面板管理链接（可添加、编辑、删除、拖拽调整顺序）
- 修改完成后点击右上角「保存到服务器」即可生效

---

## 版权说明

- 遵循原项目版权要求，请保留页脚的开源地址信息
- 请勿将本项目用于商业出售