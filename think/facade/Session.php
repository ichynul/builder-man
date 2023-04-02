<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\facade;

use Workerman\Protocols\Http\Session as BaseSession;

class Session
{
    /**
     * Undocumented function
     *
     * @return BaseSession
     */
    public static function getInstance()
    {
        return request()->session();
    }

    /**
     * 获取session_id
     * @access public
     * @return string
     */
    public static function getId(): string
    {
        return self::getInstance()->getId();
    }

    /**
     * 获取所有数据
     * @return array
     */
    public static function all(): array
    {
        return self::getInstance()->all();
    }

    /**
     * 判断session数据
     * @access public
     * @param string $name session名称
     * @return bool
     */
    public static function has(string $name): bool
    {
        return self::getInstance()->has($name);
    }

    /**
     * session获取
     * @access public
     * @param string $name    session名称
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return self::getInstance()->get($name, $default);
    }

    /**
     * session设置
     * @access public
     * @param string $name  session名称
     * @param mixed  $value session值
     * @return void
     */
    public static function set(string $name, $value): void
    {
        self::getInstance()->set($name, $value);
    }

    /**
     * session获取并删除
     * @access public
     * @param string $name session名称
     * @return mixed
     */
    public static function pull(string $name)
    {
        return self::getInstance()->pull($name);
    }

    /**
     * 添加数据到一个session数组
     * @access public
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public static function push(string $key, $value): void
    {
        $array = self::getInstance()->get($key, []);

        $array[] = $value;

        self::getInstance()->set($key, $array);
    }

    /**
     * 删除session数据
     * @access public
     * @param string $name session名称
     * @return void
     */
    public static function delete(string $name): void
    {
        self::getInstance()->delete($name);
    }

    /**
     * 清空session数据
     * @access public
     * @return void
     */
    public static function clear(): void
    {
        self::getInstance()->flush();
    }

    /**
     * 销毁session
     */
    public static function destroy(): void
    {
        self::getInstance()->save();
        self::getInstance()->flush();
    }
}
