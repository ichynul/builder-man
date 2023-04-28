<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\facade;

use think\Facade;

/**
 * @see \think\Lang
 * @package think\facade
 * @mixin \think\Lang
 * @method static void setLangSet(string $lang) 设置当前语言
 * @method static string getLangSet() 获取当前语言
 * @method static string defaultLangSet() 获取默认语言
 * @method static array load(string|array $file, string $range = '') 加载语言定义(不区分大小写)
 * @method static bool has(string|null $name, string $range = '') 判断是否存在语言定义(不区分大小写)
 * @method static mixed get(string|null $name = null, array $vars = [], string $range = '') 获取语言定义(不区分大小写)
 * @method static string detect(\think\Request $request) 自动侦测设置获取语言选择
 * @method static void saveToCookie(\think\Cookie $cookie) 保存当前语言到Cookie
 */
class Lang extends Facade
{
    /**
     * Undocumented variable
     *
     * @var \think\Lang
     */
    protected static $instance;

    /**
     * Undocumented function
     *
     * @return \think\Lang
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new \think\Lang;
        }

        return self::$instance;
    }

    public static function destroyInstance()
    {
        if (self::$instance) {
            self::$instance = null;
        }
    }

    // 调用实际类的方法
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::getInstance(), $method], $params);
    }
}
