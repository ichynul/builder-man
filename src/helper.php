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

use support\Log;
use think\route\Url;
use Webman\Http\Response;

if (!function_exists('trace')) {

    function trace($log)
    {
        Log::info($log);
    }
}

if (!function_exists('input')) {

    /**
     * 获取输入数据 支持默认值和过滤
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
     * @return mixed
     */
    function input($key = '', $default = null)
    {
        if (0 === strpos($key, '?')) {
            $key = substr($key, 1);
            $data = request()->all();
            return isset($data[$key]);
        }

        if ($pos = strpos($key, '/')) {
            $key = substr($key, 0, $pos);
        }
        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            $method = substr($key, 0, $pos);
            if (in_array($method, ['get', 'post', 'header', 'file'])) {
                $key = substr($key, $pos + 1);
            } else {
                $method = 'all';
            }

            return request()->$method($key, $default);
        }

        return request()->input($key, $default);
    }
}

if (!function_exists('url')) {

    /**
     * Url生成
     * @param string      $url    路由地址
     * @param array       $vars   变量
     * @return Url
     */
    function url($url = '', $vars = [])
    {
        $url = ltrim($url, '/');
        $path = ltrim(request()->path(), '/');

        $arr1 = explode('/', $url);
        $arr2 = explode('/', $path);

        $isPlugin = false;

        if (isset($arr1[1]) && $arr1[0] === 'app') {
            $arr2 = $arr1;
        } else if (count($arr1) >= 4) {
            $arr2 = $arr1;
        } else if (!isset($arr2[0])) {
            $arr2 = $arr1;
        } else {
            if ($arr2[0] === 'app') {
                array_shift($arr2);
                $isPlugin = true;
            }
            $arr2[1] = !empty($arr2[1]) ? $arr2[1] : 'index';
            $arr2[2] = !empty($arr2[2]) ? $arr2[2] : 'index';
            if (count($arr1) == 1) {
                $arr2 = [$arr2[0], $arr2[1], $arr1[0]];
            } else if (count($arr1) == 2) {
                $arr2 = [$arr2[0], $arr1[0], $arr1[1]];
            } else if (count($arr1) >= 3) {
                $arr2 = [$arr1[0], $arr1[1], $arr1[2]];
            }
        }

        $url =  strtolower('/' . implode('/', $arr2));

        if ($isPlugin) {
            $url = '/app' . $url;
        }

        $url = count($vars) > 0 ? $url . '?' . http_build_query($vars) : $url;

        return new Url($url);
    }
}

if (!function_exists('download')) {
    /**
     * @param string $filename 要下载的文件
     * @param string $name     显示文件名
     * @return Response
     */
    function download(string $filename, string $name)
    {
        $response = new Response;
        $response->download($filename, $name);

        return $response;
    }
}
