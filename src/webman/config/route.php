<?php

/**
 *builder 相关路由.
 */

use Webman\Route;
use tpext\webman\AdminAuth;

Route::group('/app/admin', function () {
    Route::any('/upload/upfiles', [tpext\builder\admin\controller\Upload::class, 'upfiles']);
    Route::any('/upload/base64', [tpext\builder\admin\controller\Upload::class, 'base64']);
    Route::any('/import/page', [tpext\builder\admin\controller\Import::class, 'page']);
    Route::any('/import/aftersuccess', [tpext\builder\admin\controller\Import::class, 'aftersuccess']);
    Route::any('/attachment/choose', [tpext\builder\admin\controller\Attachment::class, 'choose']);
    Route::any('/attachment/uploadsuccess', [tpext\builder\admin\controller\Attachment::class, 'uploadsuccess']);
    Route::any('/attachment/index', [tpext\builder\admin\controller\Attachment::class, 'index']);
    Route::any('/attachment/export', [tpext\builder\admin\controller\Attachment::class, 'export']);
    Route::any('/attachment/selectpage', [tpext\builder\admin\controller\Attachment::class, 'selectpage']);
    Route::any('/attachment/autopost', [tpext\builder\admin\controller\Attachment::class, 'autopost']);
})->middleware([AdminAuth::class]);

Route::group('/index', function () {
    Route::any('/file/extimg', [tpext\builder\index\controller\File::class, 'extimg']);
});
