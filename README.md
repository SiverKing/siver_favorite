# 🚀 Siver Favorite - 简约导航/收藏夹(带后台)

基于「简约导航开源」项目二次开发，优化页面显示，增加后台管理 | **原项目地址：[appexplore/jianavi](https://github.com/appexplore/jianavi)**

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
在后台中可以对链接、分类进行增删查改，修改后点击后台页面最下方保存到服务器按钮即可

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

## ⚠ 版权声明
> 根据开源协议要求：  
> 1. 请保留页脚原始项目地址链接
> 2. 禁止任何形式的代码转售行为
> 
> 其他部分欢迎自由修改，期待您的二次创作！
