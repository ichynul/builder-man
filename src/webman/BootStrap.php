<?php

namespace tpext\webman;

use tpext\common\ExtLoader;
use tpext\think\App;
use think\facade\Cache;

class BootStrap implements \Webman\Bootstrap
{
    public static function start($worker)
    {
        if ($worker->name == 'monitor') {
            return;
        }

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

        echo '注册扩展目录:extend/成功,composer.json文件已修改' . "\n";

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
