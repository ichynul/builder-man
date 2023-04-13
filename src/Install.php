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
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
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
