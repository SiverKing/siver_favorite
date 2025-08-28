# 🚀 Siver Favorite - 简约导航/收藏夹(带后台)

基于「简约导航开源」项目二次开发，优化页面显示，增加后台管理 | **原项目地址：[appexplore/jianavi](https://github.com/appexplore/jianavi)**  
演示站：https://www.siver.top/favorite/

![PHP>=7.0](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg) 
![Static Site](https://img.shields.io/badge/架构-纯静态站点-brightgreen.svg)

## 🛠 快速部署
1. 下载项目ZIP包并解压
2. 上传至支持PHP的服务器空间
3. 直接访问 `index.html` 即可使用

> 新手教程推荐：  
> 📖 [网站搭建入门指南](https://zhuanlan.zhihu.com/p/44102948)  
> 🆓 [免费主机获取攻略](https://zhuanlan.zhihu.com/p/44099866)

## 🔑 后台管理
访问地址：`你的域名/admin`  
默认凭证：`admin` / `123456`  
在后台中可以对链接、分类进行增删改，修改后点击后台页面最下方`保存到服务器`按钮即可

### 配置修改指南
编辑服务器上的 `config.php`：
```php
<?php
return [
    'username' => 'admin',  //账号
    'password' => '123456'  //密码
];
?>
```

## 基于原项目的修改内容  
**前端优化:**  
  重构页面布局为瀑布流模式，实现分类内容不足时自动填充下方空白区域  
  
**后台功能:**  
  基础账号体系：支持账号密码登录验证  
  内容管理功能：  
  • 增删改基础内容操作  
  • 二次确认机制：需手动点击"保存到服务器"按钮方可同步到`index.html`  
  实现原理：通过直接修改前端HTML代码实现内容更新  

## ⚠ 版权声明
> 根据开源协议要求：  
> 1. 请保留页脚原始项目地址链接
> 2. 禁止任何形式的代码转售行为
> 
> 其他部分欢迎自由修改，期待您的二次创作！
