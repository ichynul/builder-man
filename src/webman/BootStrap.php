<?php

namespace tpext\webman;

use think\Validate;
use tpext\think\App;
use tpext\think\View;
use think\facade\Lang;
use think\facade\Cache;
use tpext\common\ExtLoader;
use tpext\webman\MinifyTool;
use tpext\common\RouteLoader;
use tpext\builder\common\Builder;
use tpext\builder\common\Module as builderModule;

class BootStrap implements \Webman\Bootstrap
{
    public static function start($worker)
    {
        if ($worker->name == 'monitor') {
            return;
        }

        Validate::maker(function (Validate $validate) {
            $validate->setLang(Lang::getInstance());
        });

        Lang::load(BuilderMan::getInstance()->getRoot() . implode(DIRECTORY_SEPARATOR, ['think', 'lang', App::getDefaultLang() . '.php']));

        static::setupBuilder();

        if (Cache::get('builder_man_init')) {
            ExtLoader::bindExtensions();
            return;
        }
        static::composer();
        static::getExtendExtensions();
        ExtLoader::bindExtensions();
        RouteLoader::load();
        
        Cache::set('builder_man_init', true, 60);
    }

    public static function composer()
    {
        if (!is_dir(base_path() . '/extend/')) {
            mkdir(base_path() . '/extend/', 0775);
        }

        $json = json_decode(file_get_contents(base_path() . '/composer.json'), true);

        $rewrite = false;
        if (empty($json['autoload'])) {
            $json['autoload'] = [
                "psr-0" => [
                    "" => "extend/"
                ]
            ];
            $rewrite = true;
        } else {
            if (empty($json['autoload']['psr-0'])) {
                $json['autoload']['psr-0'] = [
                    "" => "extend/"
                ];
                $rewrite = true;
            } else {
                if (!in_array('extend/', $json['autoload']['psr-0'])) {
                    $json['autoload']['psr-0'][''] = "extend/";
                    $rewrite = true;
                }
            }
        }

        if (!$rewrite) {
            return;
        }

        echo '[builder-man] regist path `extend/` succeeded, composer.json was updated .' . "\n";

        file_put_contents(base_path() . '/composer.json', json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    public static function getExtendExtensions()
    {
        $data = Cache::get('tpext_extend_extensions');

        if ($data) {
            return $data;
        }

        $data = static::scanExtends(App::getRootPath() . 'extend');

        if (empty($data)) {
            $data = ['empty'];
        }

        Cache::set('tpext_extend_extensions', $data, 60);

        return $data;
    }

    protected static function scanExtends($path, $extends = [])
    {
        if (!is_dir($path)) {
            return [];
        }

        $dir = opendir($path);

        $reflectionClass = null;

        $sonDir = null;

        while (false !== ($file = readdir($dir))) {

            if (($file != '.') && ($file != '..') && ($file != '.git')) {

                $sonDir = $path . DIRECTORY_SEPARATOR . $file;

                if (is_dir($sonDir)) {
                    $extends = array_merge($extends, static::scanExtends($sonDir));
                } else {

                    if (preg_match('/.+?\\\extend\\\(.+?)\.php$/i', str_replace('/', '\\', $sonDir), $mtches)) {

                        $content = file_get_contents($sonDir); //通过文件内容判断是否为扩展。class_exists方式的$autoload有点问题

                        if (
                            preg_match('/is_tpext_extension/i', $content) //在扩展中加个注释表明是扩展。如下：
                            //is_tpext_extension
                            /*is_tpext_extension*/
                            ||
                            (preg_match('/\$version\s*=/i', $content)
                                && preg_match('/\$name\s*=/i', $content)
                                && preg_match('/\$title\s*=/i', $content)
                                && preg_match('/\$description\s*=/i', $content)
                                && preg_match('/\$root\s*=/i', $content)
                            )
                        ) {
                            $extends[] = $mtches[1];
                        }
                    }
                }
            }
        }

        closedir($dir);

        unset($reflectionClass, $sonDir);

        return $extends;
    }

    protected static function setupBuilder()
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
    }
}
