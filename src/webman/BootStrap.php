<?php

namespace tpext\webman;

use think\Validate;
use tpext\think\App;
use think\facade\Lang;
use think\facade\Cache;
use tpext\common\ExtLoader;

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

        if (Cache::get('builder_man_init')) {
            return;
        }
        static::composer();
        static::getExtendExtensions();
        ExtLoader::bindExtensions();

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

        echo 'regist path [/extend] succeeded, composer.json was updated' . "\n";

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
}
