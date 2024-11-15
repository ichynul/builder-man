<?php

/**
 *builder 相关路由.
 */

use Webman\Route;

Route::group('/index', function () {
    Route::any('/file/extimg', [tpext\builder\index\controller\File::class, 'extimg']);
});
