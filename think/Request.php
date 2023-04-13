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

namespace think;

use Workerman\Worker;

class Request extends \Webman\Http\Request
{
    protected $method = '';

    /**
     * 当前SERVER参数
     * @var array
     */
    protected $server = [];
    /**
     * 当前REQUEST参数
     * @var array
     */
    protected $request = [];
    /**
     * 是否合并Param
     * @var bool
     */
    protected $mergeParam = false;
    /**
     * 当前请求参数
     * @var array
     */
    protected $param = [];

    protected $routeParam = null;

    protected $controllerName = '';

    public function decode()
    {
        $_POST = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = array();

        $_GET = parent::get() ?: [];
        $_COOKIE = parent::cookie() ?: [];
        $_SESSION = parent::session()->all() ?: [];

        $httpHost = parent::header('Host');
        $httpArr = explode(':', $httpHost);

        $microtime = \microtime(true);

        $_SERVER = array(
            'QUERY_STRING'         => parent::queryString(),
            'REQUEST_METHOD'       => strtoupper(parent::method()),
            'REQUEST_URI'          => parent::uri(),
            'SERVER_PROTOCOL'      => 'HTTP/' . parent::parseProtocolVersion(),
            'SERVER_SOFTWARE'      => 'workerman/' . Worker::VERSION,
            'SERVER_NAME'          => $httpArr[0],
            'SERVER_PORT'          => $httpArr[1] ?? 80,
            'HTTP_HOST'            => $httpHost,
            'HTTP_USER_AGENT'      => parent::header('User-Agent'),
            'HTTP_ACCEPT'          => parent::header('Accept', ''),
            'HTTP_ACCEPT_LANGUAGE' => parent::header('Accept-Language', ''),
            'HTTP_ACCEPT_ENCODING' => parent::header('Accept-Encoding', ''),
            'HTTP_COOKIE'          => parent::header('Cookie', ''),
            'HTTP_CONNECTION'      => parent::header('Connection', ''),
            'CONTENT_TYPE'         => parent::header('content-type', ''),
            'CONTENT_LENGTH'       => parent::header('Content-Length', ''),
            'REMOTE_ADDR'          => $this->connection->getRemoteIp(),
            'REMOTE_PORT'          => $this->connection->getRemotePort(),
            'REQUEST_TIME'         => (int)$microtime,
            'REQUEST_TIME_FLOAT'   => $microtime //compatible php5.4
        );

        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $_POST = parent::post() ?: [];
        }

        $GLOBALS['HTTP_RAW_REQUEST_DATA'] = $GLOBALS['HTTP_RAW_POST_DATA'] = $this->rawBody();

        $_REQUEST = \array_merge($_GET, $_POST);

        $this->server = $_SERVER;
        $this->request = $_REQUEST;

        if ($this->route) {
            $path = strtolower($this->route->getPath());
            $explode = explode('/', trim($path, '/'));
            $this->app = $explode[0] ?: 'index';
            $this->controllerName  = $explode[1] ?? 'index';
            $this->action  = $explode[2] ?? 'index';
        } else {
            $path = strtolower($this->path());
            $explode = explode('/', trim($path, '/'));
            $this->app = $explode[0] ?: 'index';
            $this->controllerName  = $explode[1] ?? 'index';
            $this->action  = $explode[2] ?? 'index';
        }
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function method($origin = false)
    {
        $method = strtoupper(parent::method());

        if ($origin) {
            return $method ?: 'GET';
        }

        if ($this->method) {
            return $this->method;
        }

        if ($method == 'GET') {
            $this->method = 'GET';
        } else if ($method == 'POST') {

            $this->method = 'POST';

            if (!isset($this->_data['post'])) {
                $this->parsePost();
            }

            if (isset($this->_data['post']['_method'])) {
                $method = strtolower($this->_data['post']['_method']);
                unset($this->_data['post']['_method']);
                if (in_array($method, ['put', 'patch', 'delete'])) {
                    $this->method = strtoupper($method);
                }
            }
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli(): bool
    {
        return true;
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi(): bool
    {
        return false;
    }

    /**
     * 获取GET参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (!isset($this->_data['get'])) {
            $this->parseGet();
        }

        if (is_array($name)) {
            return $this->_only($name, $this->_data['get'], $filter);
        }

        return $this->_input($this->_data['get'], $name, $default, $filter);
    }

    /**
     * 获取POST参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (!isset($this->_data['post'])) {
            $this->parsePost();
        }

        if (is_array($name)) {
            return $this->_only($name, $this->_data['post'], $filter);
        }

        return $this->_input($this->_data['post'], $name, $default, $filter);
    }

    /**
     * 获取PUT参数
     * @access public
     * @param  string|false      $name 变量名
     * @param  mixed             $default 默认值
     * @param  string|array      $filter 过滤方法
     * @return mixed
     */
    public function put($name = '', $default = null, $filter = '')
    {
        return $this->post($name, $default, $filter);
    }

    /**
     * 获取DELETE参数
     * @access public
     * @param  string|false      $name 变量名
     * @param  mixed             $default 默认值
     * @param  string|array      $filter 过滤方法
     * @return mixed
     */
    public function delete($name = '', $default = null, $filter = '')
    {
        return $this->post($name, $default, $filter);
    }

    /**
     * 获取PATCH参数
     * @access public
     * @param  string|false      $name 变量名
     * @param  mixed             $default 默认值
     * @param  string|array      $filter 过滤方法
     * @return mixed
     */
    public function patch($name = '', $default = null, $filter = '')
    {
        return $this->post($name, $default, $filter);
    }

    /**
     * 获取当前请求的参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->mergeParam)) {

            $method = $this->method(true);

            if ($method == 'POST') {
                $this->param = array_merge(parent::post(null, []), parent::get(null, []));
            } else {
                $this->param = parent::get(null, []);
            }

            $this->mergeParam = true;
        }

        if (is_array($name)) {
            return $this->_only($name, $this->param, $filter);
        }

        return $this->_input($this->param, $name, $default, $filter);
    }

    /**
     * 获取request变量
     * @access public
     * @param  string|array $name 数据名称
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->_only($name, $this->request, $filter);
        }

        return $this->_input($this->request, $name, $default, $filter);
    }

    /**
     * 获取server参数
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function server(string $name = '', string $default = '')
    {
        if (empty($name)) {
            return $this->server;
        } else {
            $name = strtoupper($name);
        }

        return $this->server[$name] ?? $default;
    }

    /**
     * 获取路由参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function route($name = '', $default = null, $filter = '')
    {
        if (!parent::$route) {
            return $default;
        }

        if (is_null($this->routeParam)) {
            $this->routeParam = $this->route->param();
        }

        if (is_array($name)) {
            return $this->_only($name, $this->routeParam, $filter);
        }

        return $this->_input($this->routeParam, $name, $default, $filter);
    }

    protected function filterData($data, $filter, $name, $default)
    {
        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        return $data;
    }

    protected function getFilter($filter, $default): array
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;

        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @access public
     * @param  mixed $value 键值
     * @param  mixed $key 键名
     * @param  array $filters 过滤方法+默认值
     * @return mixed
     */
    public function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);

        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (is_string($filter) && false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * 是否存在某个请求参数
     * @access public
     * @param  string    $name 变量名
     * @param  string    $type 变量类型
     * @param  bool      $checkEmpty 是否检测空值
     * @return mixed
     */
    public function has($name, $type = 'param', $checkEmpty = false)
    {
        if (!in_array($type, ['param', 'get', 'post', 'request', 'put', 'patch', 'file', 'session', 'cookie', 'header'])) {
            return false;
        }

        $param = $this->$type;

        // 按.拆分成多维数组进行判断
        foreach (explode('.', $name) as $val) {
            if (isset($param[$val])) {
                $param = $param[$val];
            } else {
                return false;
            }
        }

        return ($checkEmpty && '' === $param) ? false : true;
    }

    /**
     * 获取指定的参数
     * @access public
     * @param  string|array  $name 变量名
     * @return mixed
     */
    public function only(array $name): array
    {
        return $this->_only($name, $this->param());
    }

    protected function _input(array $data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }

        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                [$name, $type] = explode('/', $name);
            }

            $data = $this->getData($data, $name);

            if (is_null($data)) {
                return $default;
            }

            if (is_object($data)) {
                return $data;
            }
        }

        $data = $this->filterData($data, $filter, $name, $default);

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    /**
     * 获取指定的参数
     * @access public
     * @param  array        $name 变量名
     * @param  mixed        $data 数据或者变量类型
     * @param  string|array $filter 过滤方法
     * @return array
     */
    public function _only(array $name, $data = 'param', $filter = ''): array
    {
        $data = is_array($data) ? $data : $this->$data();

        $item = [];
        foreach ($name as $key => $val) {

            if (is_int($key)) {
                $default = null;
                $key     = $val;
                if (!isset($data[$key])) {
                    continue;
                }
            } else {
                $default = $val;
            }

            $item[$key] = $this->filterData($data[$key] ?? $default, $filter, $key, $default);
        }

        return $item;
    }

    /**
     * 获取数据
     * @access public
     * @param  array         $data 数据源
     * @param  string|false  $name 字段名
     * @return mixed
     */
    protected function getData(array $data, $name)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return;
            }
        }

        return $data;
    }

    /**
     * 强制类型转换
     * @access protected
     * @param  mixed  $data
     * @param  string $type
     * @return mixed
     */
    protected function typeCast(&$data, string $type)
    {
        switch (strtolower($type)) {
                // 数组
            case 'a':
                $data = (array) $data;
                break;
                // 数字
            case 'd':
                $data = (int) $data;
                break;
                // 浮点
            case 'f':
                $data = (float) $data;
                break;
                // 布尔
            case 'b':
                $data = (bool) $data;
                break;
                // 字符串
            case 's':
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
                break;
        }
    }

    /**
     * 当前是否JSON请求
     * @access public
     * @return bool
     */
    public function isJson(): bool
    {
        $acceptType = $this->type();

        return false !== strpos($acceptType, 'json');
    }

    /**
     * 当前请求的资源类型
     * @access public
     * @return string
     */
    public function type(): string
    {
        return $this->header('accept', '');
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax(bool $ajax = false): bool
    {
        $result = parent::isAjax();

        if (true === $ajax) {
            return $result;
        }

        return $this->param('_ajax') ? true : $result;
    }

    /**
     * 当前是否Pjax请求
     * @access public
     * @param  bool $pjax true 获取原始pjax请求
     * @return bool
     */
    public function isPjax(bool $pjax = false): bool
    {
        $result = parent::isPjax();

        if (true === $pjax) {
            return $result;
        }

        return $this->param('_pajax') ? true : $result;
    }

    /**
     * 获取客户端IP地址
     * @access public
     * @return string
     */
    public function ip(): string
    {
        return parent::getRealIp(true);
    }

    /**
     * 当前请求URL地址中的port参数
     * @access public
     * @return int
     */
    public function port(): int
    {
        return parent::getLocalPort();
    }

    /**
     * 获取当前完整URL 包括QUERY_STRING
     * @access public
     * @return string
     */
    public function url(): string
    {
        return parent::url();
    }
    
    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType(): string
    {
        $contentType = $this->header('content-type', '');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                [$type] = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }

    /**
     * 设置GET数据
     * @access public
     * @param  array $get 数据
     * @return $this
     */
    public function withGet(array $get)
    {
        $this->_data['get'] = array_merge($this->get(), $get);
        return $this;
    }

    /**
     * 设置POST数据
     * @access public
     * @param  array $post 数据
     * @return $this
     */
    public function withPost(array $post)
    {
        $this->_data['post'] = array_merge($this->post(), $post);
        return $this;
    }

    /**
     * 设置当前的操作名
     * @access public
     * @param  string $action 操作名
     * @return $this
     */
    public function setAction(string $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * 获取当前的控制器名
     * @access public
     * @param  bool $convert 转换为小写
     * @return string
     */
    public function controller(bool $convert = false): string
    {
        $name = $this->controllerName ?: '';
        return $convert ? strtolower($name) : $name;
    }

    /**
     * 获取当前的操作名
     * @access public
     * @param  bool $convert 转换为小写
     * @return string
     */
    public function action(bool $convert = false): string
    {
        $name = $this->action ?: '';
        return $convert ? strtolower($name) : $name;
    }
}
