<?php

namespace tpext\webman;

use Webman\Http\Request;
use think\facade\Validate;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * for webman
 */

class Setup implements MiddlewareInterface
{
    protected $module = '';
    protected $controller = '';
    protected $action = '';

    public function process(Request $request, callable $next): Response
    {
        $response = $next($request);
        Validate::destroyInstance();
        return $response;
    }
}
