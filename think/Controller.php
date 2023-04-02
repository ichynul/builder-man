<?php

namespace think;

use support\Request;
use think\Validate;
use think\helper\Str;
use tpext\think\View;
use Webman\Http\Response;
use tpext\webman\BuilderMan;
use think\exception\ValidateException;
use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
abstract class Controller
{
    protected $vars  = [];

    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    protected static $dispatchJumpTemplate = '';

    public function __construct()
    {
        $this->request = request();
        $this->initialize();
    }

    /**
     * 初始化,兼容tp框架
     *
     * @return void|Response
     */
    protected function initialize()
    {
        //子类重写此方法
    }

    public static function setDispatchJumpTemplate($template)
    {
        self::$dispatchJumpTemplate = $template;
    }

    public static function getDispatchJumpTemplate()
    {
        if (!self::$dispatchJumpTemplate) {
            $rootPath = BuilderMan::getInstance()->getRoot();
            self::$dispatchJumpTemplate = $rootPath . implode(DIRECTORY_SEPARATOR, ['think', 'tpl', 'dispatch_jump']) . '.tpl';
        }

        return self::$dispatchJumpTemplate;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (!$v->failException(false)->check($data)) {
            return $v->getError();
        }

        return true;
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name  类名
     * @return string
     */
    public function parseClass(string $layer, string $name): string
    {
        $name  = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path  = $array ? implode('\\', $array) . '\\' : '';

        $arr = explode('controller', get_called_class());

        if (class_exists($arr[0] . $layer . '\\' . $path . $class)) {
            return $arr[0] . $layer . '\\' . $path . $class;
        }

        return 'app\\common\\' . $layer . '\\' . $path . $class;
    }

    /** tp兼容 **/

    /**
     * 渲染模板输出
     * @param string   $template 模板文件
     * @param array    $vars     模板变量
     * @param int      $code     状态码
     * @param callable $filter   内容过滤
     * @return View
     */
    protected function fetch(string $template = '', $vars = [])
    {
        $view = new View($template, array_merge($this->vars, $vars));

        return new Response(200, [], $view->getContent());
    }

    /**
     * 渲染模板输出
     * @param string   $content 渲染内容
     * @param array    $vars    模板变量
     * @param int      $code    状态码
     * @param callable $filter  内容过滤
     * @return View
     */
    protected function display(string $content, $vars = [])
    {
        $view = new View($content, array_merge($this->vars, $vars));

        $view->isContent();

        return new Response(200, [], $view->getContent());
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, $header = [])
    {
        if (is_null($url) && $referer = request()->header('REFERER')) {
            $url = $referer;
        } elseif ('' !== $url) {
            $url = (string) $url;
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url)->__toString();
        }

        $result = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];

        $response = null;

        if ($this->getResponseType() == 'json') {
            $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $view = new View(self::getDispatchJumpTemplate(), $result);
            $response = new Response(200, $header, $view->getContent());
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, $header = [])
    {
        $type = $this->getResponseType();

        if (is_null($url)) {
            $url = $type == 'json' ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (string) $url;
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url)->__toString();
        }

        $result = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];

        $response = null;

        if ($type == 'json') {
            $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $view = new View(self::getDispatchJumpTemplate(), $result);
            $response = new Response(200, $header, $view->getContent());
        }

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param  mixed     $data 要返回的数据
     * @param  integer   $code 返回的code
     * @param  mixed     $msg 提示信息
     * @param  string    $type 返回数据格式
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function result($data, $code = 0, $msg = '', $type = '', $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $response = null;

        if ($this->getResponseType() == 'json') {
            $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {

            $view = new View(self::getDispatchJumpTemplate(), $result);
            $response = new Response(200, $header, $view->getContent());
        }

        throw new HttpResponseException($response);
    }

    /**
     * URL重定向
     * @access protected
     * @param  string         $url 跳转的URL表达式
     * @param  array|integer  $params 其它URL参数
     * @param  integer        $code http code
     * @param  array          $header 其它header参数
     * @return void
     */
    protected function redirect($url, $params = [], $code = 302, $header = [])
    {
        $response = new Response($code, ['Location' => $url . ($params ? '?' . http_build_query($params) : '')]);

        if (!empty($header)) {
            $response->withHeaders($header);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $isJson = request()->expectsJson();

        return $isJson ? 'json' : 'html';
    }

    /**
     * 清除tpextbuilder状态
     *
     * @return void
     */
    protected function destroyBuilder()
    {
        if (method_exists($this, '_destroyBuilder')) {
            $this->_destroyBuilder();
            return;
        }
    }
}
