<?php

namespace tpext\webman;

use tpext\think\View;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use tpext\builder\common\Builder;
use tpext\webman\MinifyTool;
use tpext\builder\common\Module as builderModule;

/**
 * for webman
 */

class Setup implements MiddlewareInterface
{
    protected $module = '';
    protected $controller = '';
    protected $action = '';

    public function process(Request $request, callable $next): Response
    {
        $instance = BuilderMan::getInstance();
        $rootPath = $instance->getRoot();
        $instance->copyAssets();
        $admin_layout = $rootPath . implode(DIRECTORY_SEPARATOR, ['src', 'view', 'layout.html']);

        $assets_ver = config('plugin.builder.man.app.assets_ver', '1.0');

        if (config('plugin.builder.man.app.minify', false)) {
            $tool = new MinifyTool;
            $tool->minify();
        }

        $css = MinifyTool::getCss();
        $js = MinifyTool::getJs();

        foreach ($css as &$c) {
            if (strpos($c, '?') == false && strpos($c, 'http') == false) {
                $c .= '?aver=' . $assets_ver;
            }
        }

        unset($c);

        foreach ($js as &$j) {
            if (strpos($j, '?') == false && strpos($j, 'http') == false) {
                $j .= '?aver=' . $assets_ver;
            }
        }

        unset($j);
        Builder::aver($assets_ver);
        Builder::auth(config('plugin.builder.man.app.auth_class', ''));
        View::share(
            [
                'admin_page_position' => '',
                'admin_page_title' => config('plugin.builder.man.app.name', 'Webman后台管理系统'),
                'admin_page_description' => config('plugin.builder.man.app.name', 'Webman后台管理系统'),
                'admin_js' => $js,
                'admin_css' => $css,
                'admin_layout' => $admin_layout,
                'admin_assets_ver' => $assets_ver,
            ]
        );

        builderModule::getInstance()->setUploadUrl(config('plugin.builder.man.app.upload_url', ''));
        builderModule::getInstance()->setImportUrl(config('plugin.builder.man.app.import_url', ''));
        builderModule::getInstance()->setChooseUrl(config('plugin.builder.man.app.choose_url', ''));

        return $next($request);
    }
}
