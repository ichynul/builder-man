<?php

namespace tpext\webman;

use Webman\Http\Request;
use think\facade\Session;
use Webman\Http\Response;
use plugin\admin\api\Auth;
use Webman\MiddlewareInterface;

/**
 * Admin鉴权中间件
 */
class AdminAuth implements MiddlewareInterface
{
    /**
     * 鉴权
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        $controller = $request->controller;
        $action = $request->action;
        $code = 0;
        $msg = '';
        if (!Auth::canAccess($controller, $action, $code, $msg)) {
            $result = [
                'code' => 0,
                'msg' => $msg,
                'data' => null,
                'url' => '',
                'wait' => 3,
            ];

            return json($result);
        }

        $admin_user = Session::get('admin_user');
        if (!$admin_user) {
            $admin = Session::get('admin');
            $roles = $admin['roles'];
            Session::set('admin_user', ['id' => $admin['id'], 'role_id' => in_array(1, $roles) ? 1 : 0]);
        }
        return $next($request);
    }
}
