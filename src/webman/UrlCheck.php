<?php

namespace tpext\webman;

use tpext\builder\inface\Auth as Authinface;
use plugin\admin\api\Auth;

/**
 * url鉴权
 */
class UrlCheck implements Authinface
{
    /**
     * Undocumented function
     *
     * @param string $url
     * @return boolean
     */
    public static function checkUrl($url)
    {
        $url = ltrim($url, '/');
        $arr1 = explode('/', $url);

        if (isset($arr1[1]) && $arr1[0] === 'app') {
            array_shift($arr1);
        }

        $arr1[1] = !empty($arr1[1]) ? $arr1[1] : 'index';
        $arr1[2] = !empty($arr1[2]) ? $arr1[2] : 'index';

        $code = 0;
        $msg = '';

        if (!Auth::canAccess($arr1[1], $arr1[2], $code, $msg)) {
            return false;
        }

        return true;
    }
}
