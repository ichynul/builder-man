<?php

use support\Log;
use think\route\Url;
use Webman\Http\Response;

if (!function_exists('trace')) {

    function trace($log)
    {
        Log::info($log);
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
        $url = trim($url, '/');
        $path = trim(request()->path(), '/');

        $arr1 = explode('/', $url);
        $arr2 = explode('/', $path);

        $isPlugin = false;

        if ($arr1[0] === 'app') {
            array_shift($arr1);
            $isPlugin = true;
            $arr1[1] = !empty($arr1[1]) ? $arr1[1] : 'index';
            $arr1[2] = !empty($arr1[2]) ? $arr1[2] : 'index';
            $arr2 = [$arr1[0], $arr1[1], $arr1[2]];
        } else if (count($arr1) >= 3) {
            $arr2 = [$arr1[0], $arr1[1], $arr1[2]];
        } else {
            if ($arr2[0] === 'app') {
                array_shift($arr2);
                $isPlugin = true;
            }
            $arr2[0] = !empty($arr2[0]) ? $arr2[0] : 'index';
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
