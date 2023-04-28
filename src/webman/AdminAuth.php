<?php

namespace tpext\webman;

use tpext\think\App;
use think\facade\Lang;
use Webman\Http\Request;
use think\facade\Session;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use tpext\builder\common\Module;

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

        Lang::load(Module::getInstance()->getRoot() . 'src' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . App::getDefaultLang() . '.php');

        $response = $next($request);

        Lang::destroyInstance();

        return $response;
    }
}
