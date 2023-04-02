<?php

return [
    'enable' => true,
    //
    'name' => 'Webman后台管理系统',
    'description' => 'Webman后台管理系统',
    'favicon' => '/assets/lightyearadmin/favicon.ico',
    'assets_ver' => '1.0',
    'auth_class' => \tpext\webman\UrlCheck::class, //权限检测类，按钮对无权限者隐藏。类需实现 \tpext\builder\inface\Auth 接口
    'minify' => false, //资源压缩
    'upload_url' => '/builder/upload/upfiles', //上传文件url地址
    'import_url' => '/builder/import/page', //文件导入url地址
    'choose_url' => '/builder/attachment/index', //选择文件url地址
];
