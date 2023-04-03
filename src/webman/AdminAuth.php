<?php

namespace tpext\webman;

use Webman\Http\Request;
use think\facade\Session;
use Webman\Http\Response;
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
        $admin = admin();
        if (!$admin) {
            $result = [
                'code' => 0,
                'msg' => '未登录',
                'data' => null,
                'url' => '',
                'wait' => 3,
            ];

            return json($result);
        }

        $admin_user = Session::get('admin_user');
        if (!$admin_user) {
            $roles = $admin['roles'];
            Session::set('admin_user', ['id' => $admin['id'], 'role_id' => in_array(1, $roles) ? 1 : 0]);
        }
        return $next($request);
    }
}
