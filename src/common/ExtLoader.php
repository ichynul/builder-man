<?php

namespace tpext\common;

use think\facade\Cache;
use Webman\Event\Event;

class ExtLoader
{
    /**
     * Undocumented variable
     *
     * @var string[]
     */
    private static $classMap = [];
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
                $instance->loaded();
                self::trigger('tpext_extension_loaded_' . $declare);
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
}
