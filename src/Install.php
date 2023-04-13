<?php

namespace tpext;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = array(
        'webman/config/app.php' => 'config/plugin/builder/man/app.php',
        'webman/config/lang.php' => 'config/plugin/builder/man/lang.php',
        'webman/config/bootstrap.php' => 'config/plugin/builder/man/bootstrap.php',
        'webman/config/middleware.php' => 'config/plugin/builder/man/middleware.php',
        'webman/config/route.php' => 'config/plugin/builder/man/route.php',
    );

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        $appConfig = file_get_contents(config_path() . '/app.php');

        file_put_contents(config_path() . '/app.php', preg_replace('/([\'\"]request_class[\'\"]\s*=>\s*)[:\w\\\]+/i', '$1Request::class', $appConfig));

        echo "use [support\\Request::class] as [request_class] in config/app.php\n";

        $request = file_get_contents(base_path() . '/support/Request.php');

        file_put_contents(base_path() . '/support/Request.php', preg_replace('/(class\s+Request\s+extends\s+)[\w\\\]+/i', '$1\\think\\Request', $request));

        echo "let [support\\Request] extends [think\\Request] in support/Request.php\n";

        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        $appConfig = file_get_contents(base_path() . '/app.php');

        file_put_contents(base_path() . '/app.php', preg_replace('/([\'\"]request_class[\'\"]\s*=>\s*)[:\w\\\]+/i', '$1Request::class', $appConfig));

        echo "use [support\\Request::class] as [request_class] in config/app.php\n";

        $request = file_get_contents(base_path() . '/support/Request.php');

        file_put_contents(base_path() . '/support/Request.php', preg_replace('/(class\s+Request\s+extends\s+)[\w\\\]+/i', '$1\\Webman\\Http\\Request', $request));

        echo "let [support\\Request] extends [Webman\\Http\\Request] in support/Request.php\n";
        
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0755, true);
                }
            }
            if (($source == 'webman/config/app.php' || $source == 'webman/config/lang.php')
                && is_file(base_path() . "/$dest")
            ) {
                continue;
            }
            copy_dir(__DIR__ . "/$source", base_path() . "/$dest");
            echo "Create $dest
";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest
";
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }
}
