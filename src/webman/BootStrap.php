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
            ExtLoader::bindExtensions();
            return;
        }
        ExtLoader::bindExtensions();
        Cache::set('builder_man_init', true, 60);
    }
}
