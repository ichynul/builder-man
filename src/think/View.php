<?php

namespace tpext\think;

use think\Template;
use think\helper\Str;

class View
{
    protected static $shareVars = [];
    protected $vars = [];
    protected $content = null;
    protected $isContent = false;

    /**
     * 原始数据
     * @var mixed
     */
    protected $data = [];

    /**
     * Undocumented variable
     *
     * @var Template
     */
    protected $engine;

    protected $config = [
        'auto_rule'     => 1,
        'view_dir_name' => 'view',
        'view_path'     => '',
        'view_suffix'   => 'html',
        'view_depr'     => DIRECTORY_SEPARATOR,
        'tpl_cache'     => true,
    ];

    public function __construct($data = '', $vars = [], $config = [])
    {
        $this->data = $data;
        $this->vars = $vars;

        $this->config['cache_path'] = App::getRuntimePath() . 'temp' . DIRECTORY_SEPARATOR;

        $this->engine = new Template(array_merge($this->config, $config));
        // $this->engine->setCache($this->app->cache);
    }

    /**
     * 获取输出数据
     * @access public
     * @return string
     */
    public function getContent()
    {
        if (null == $this->content) {
            $this->content = $this->fetch($this->data) ?: '';
        }

        return $this->content;
    }

    public function isContent($content = true)
    {
        $this->isContent = $content;
        return $this;
    }

    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    public static function share($name, $value = '')
    {
        if (is_array($name)) {
            self::$shareVars = array_merge(self::$shareVars, $name);
        } else {
            self::$shareVars[$name] = $value;
        }
    }

    public static function clearShareVars()
    {
        self::$shareVars  = [];
    }

    public function clear()
    {
        $this->vars = [];
        $this->content = null;

        return $this;
    }

    protected function fetch($template = '')
    {
        ob_start();

        $vars = array_merge(self::$shareVars, $this->vars);

        if ($this->isContent) {
            $this->engine->display($template, $vars);
        } else {
            if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
                // 获取模板文件名
                $template = $this->parseTemplate($template);
            }

            $this->engine->fetch($template, $vars);
        }

        $content = ob_get_clean();

        return $content;
    }

    /**
     * Undocumented function
     *
     * @param string $template
     * @return string
     */
    private function parseTemplate(string $template)
    {
        // 分析模板文件规则
        $request = request();

        $module = '';
        $controller = '';
        $action = '';

        if ($request->route) {
            $requestPath = strtolower($request->route->getPath());
            $explode = explode('/', trim($requestPath, '/'));
            $module = $explode[0] ?: 'index';
            $controller  = $explode[1] ?? 'index';
            $action  = $explode[2] ?? 'index';
        } else {
            $requestPath = strtolower($request->path());
            $explode = explode('/', trim($requestPath, '/'));
            $module = $explode[0] ?: 'index';
            $controller  = $explode[1] ?? 'index';
            $action  = $explode[2] ?? 'index';
        }

        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($app, $template) = explode('@', $template);
        }

        $view_dir = $this->config['view_dir_name'];

        if (isset($app)) {
            $viewPath = App::getRootPath()  . 'app' . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . $view_dir . DIRECTORY_SEPARATOR;

            if (is_dir($viewPath)) {
                $path = $viewPath;
            } else {
                $path = App::getRootPath() . $view_dir . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR;
            }

            $this->engine->view_path = $path;
        } else {
            $path = App::getRootPath()  . 'app' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $view_dir . DIRECTORY_SEPARATOR;
        }

        $depr = $this->config['view_depr'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);

            if (strpos($controller, '.')) {
                $pos        = strrpos($controller, '.');
                $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            } else {
                $controller = Str::snake($controller);
            }

            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认模板渲染规则定位
                    $template = $action;
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                } else if (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        $templateFile = $path . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');

        if (is_file($templateFile)) {
            return $templateFile;
        }

        $controllerClass = request()->controller;

        if (!$controllerClass) {

            return $template;
        }

        $reflect = new \ReflectionClass($controllerClass);      //所要查询的类名 
        $file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $reflect->getFileName());

        $arr = explode('controller', $file);

        return $arr[0] . $view_dir . DIRECTORY_SEPARATOR . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
