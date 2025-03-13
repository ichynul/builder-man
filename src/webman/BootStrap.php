<?php

namespace tpext\webman;

use tpext\think\App;
use think\facade\Lang;
use tpext\common\ExtLoader;

class BootStrap implements \Webman\Bootstrap
{
    public static function start($worker)
    {
        if ($worker->name == 'monitor') {
            return;
        }

        Lang::load(BuilderMan::getInstance()->getRoot() . implode(DIRECTORY_SEPARATOR, ['think', 'lang', App::getDefaultLang() . '.php']));

        ExtLoader::bindExtensions();
    }
}
