<?php

namespace tpext\common;

use Webman\Event\Event;
use think\facade\Cache;

class ExtLoader
{
    /**
     * Undocumented variable
     *
     * @var string[]
     */
    private static $classMap = [];

    /**
     * Undocumented variable
     *
     * @var Module[]
     */
    private static $modules = [];

    /**
     * Undocumented variable
     *
     * @var Resource[]
     */
    private static $resources = [];

    private static $watches = [];

    // 注册classmap
    public static function addClassMap($class)
    {
        if (is_array($class)) {
            self::$classMap = array_merge(self::$classMap, $class);
        } else {
            self::$classMap[] = $class;
        }
    }

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public static function getClassMap()
    {
        return self::$classMap;
    }

    /**
     * Undocumented function
     *
     * @return Module[]
     */
    public static function getModules()
    {
        return self::$modules;
    }

    /**
     * Undocumented function
     *
     * @return Resource[]
     */
    public static function getResources()
    {
        return self::$resources;
    }

    /**
     * Undocumented function
     *
     * @return Resource[]|Module[]
     */
    public static function getExtensions()
    {
        return array_merge(self::$modules, self::$resources);
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @param mixed $class
     * @param boolean $first
     * @param string $desc
     * @return void
     */
    public static function watch($name, $class, $first = false, $desc = '')
    {
        if (!isset(self::$watches[$name])) {
            self::$watches[$name] = [];
        }
        if (is_string($class) && class_exists($class)) {
            $inctance = new $class;
            $class = [$inctance, 'handle'];
        }
        self::$watches[$name][] = [$class, $desc, $first];
        Event::on($name, $class);
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @param mixed $params
     * @param boolean $once
     * @return void
     */
    public static function trigger($name, $params = null, $once = false)
    {
        Event::emit($name, $params);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public static function geWatches()
    {
        return self::$watches;
    }

    public static function bindExtensions()
    {
        self::$modules = Cache::get('tpext_modules') ?: [];
        self::$resources = Cache::get('tpext_resources') ?: [];

        foreach (self::$modules as $k => $m) {
            if (!class_exists($k, false)) {
                unset(self::$modules[$k]);
            }
        }

        foreach (self::$resources as $k => $r) {
            if (!class_exists($k, false)) {
                unset(self::$resources[$k]);
            }
        }

        if (empty(self::$modules)) {
            self::findExtensions();
            Cache::set('tpext_modules', self::$modules, 60);
            Cache::set('tpext_resources', self::$resources, 60);
        }

        foreach (self::$modules as $k => $m) {
            $m->loaded();
            self::trigger('tpext_extension_loaded_' . $k);
        }

        foreach (self::$resources as $k => $r) {
            $r->loaded();
            self::trigger('tpext_extension_loaded_' . $k);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private static function findExtensions()
    {
        self::trigger('tpext_find_extensions');

        $classMap = self::$classMap;

        foreach ($classMap as $declare) {

            if (!class_exists($declare)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($declare);

            if (!$reflectionClass->isInstantiable()) {
                continue;
            }

            if (!isset(self::$modules[$declare]) && !isset(self::$resources[$declare]) && $reflectionClass->hasMethod('extInit') && $reflectionClass->hasMethod('getInstance')) {

                $instance = $declare::getInstance();
                $instance->created();

                if (!($instance instanceof Extension)) {
                    continue;
                }
                
                $instance->install();

                if ($instance instanceof Resource) {
                    self::$resources[$declare] = $instance;
                } else if ($instance instanceof Module) {
                    self::$modules[$declare] = $instance;
                }
            }
        }
    }

    public static function isTP51()
    {
        return false;
    }

    public static function isTP60()
    {
        return true;
    }

    public static function isWebman()
    {
        return true;
    }

    /**
     * 平滑重启webman
     *
     * @param string $desc
     * @return void
     */
    public static function reloadWebman($desc = '')
    {
    }
}
