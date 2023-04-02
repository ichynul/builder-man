<?php

namespace tpext\common;

abstract class Extension
{
    protected static $extensions = [];

    protected $__root__ = null;

    protected $__ID__ = null;

    protected $__config__ = null;

    protected $__config_path__ = null;

    protected $errors = [];

    /***以下为需要设置的字段***/

    /**
     * 版本号，不必每次调整都修改。一般在[assets]静态资源、[data]数据脚本更新后修改版本号。
     *
     * @var string
     */
    protected $version = '1.0.1';

    /**
     * 扩展包类型:extend|composer
     *
     * @var string
     */
    protected $packgeType = '';

    /**
     * 名称标识 ，英文字母，如 hello.world
     *
     * @var string
     */
    protected $name = '';

    /**
     * 分类标记，用 , 分割 如 'template,mobile'
     *
     * @var string
     */
    protected $tags = '未归类';

    /**
     * 显示名称，如 你好世界
     *
     * @var string
     */
    protected $title = '未填写';

    /**
     * 显示介绍，如 你好世界是一个什么
     *
     * @var string
     */
    protected $description = '未填写';

    /**
     * 扩展根目录
     *
     * @var string
     */
    protected $root = null; // 请设置 如: __DIR__ . '/../../'

    /**
     * css\js资源路径
     * @var string
     */
    protected $assets = '';

    /**
     * 命名空间和路径，一般不用填写 如 ['namespace', 'codepath']
     *
     * @var array
     */
    protected $namespaceMap = [];

    /**
     * 版本列表，列出所有存在过的版本，即使没有升级脚本也要列出
     * 版本号 => 升级脚本
     *
     * @var array
     */
    protected $versions = [
        // '1.0.1' => '',
        // '1.0.2' => 'upgrade-1.0.2.sql',
        // '1.0.3' => '', //如果升级不涉及数据库改动，留空
    ];

    /**
     * 获取扩展包类型:extend|composer
     *
     * @return string
     */
    final public function getPackgeType()
    {
        if (empty($this->packgeType)) {
            $this->packgeType = stripos($this->getRoot(), 'vendor') === false ? 'extend' : 'composer';
        }

        return $this->packgeType;
    }

    final public function getName()
    {
        return $this->name;
    }

    final public function getTitle()
    {
        return empty($this->title) ? $this->getName() : $this->title;
    }

    final public function getDescription()
    {
        return $this->description;
    }

    final public function getTags()
    {
        return $this->tags;
    }

    final public function getVersion()
    {
        return $this->version;
    }

    final public function getId()
    {
        if (empty($this->__ID__)) {
            $this->__ID__ = strtolower(preg_replace('/\W/', '_', get_called_class()));
        }

        return $this->__ID__;
    }

    final public function getAssets()
    {
        return $this->assets;
    }

    final public static function extensionsList()
    {
        return self::$extensions;
    }

    /**
     * 获取实列
     *
     * @return static|Module|Resource
     */
    final public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$extensions[$class])) {
            $nstance = new static();
            self::$extensions[$class] = $nstance;
        }

        return self::$extensions[$class];
    }

    final public function getRoot()
    {
        if (empty($this->__root__)) {

            if (empty($this->root)) {
                throw new \UnexpectedValueException('root未设置:' . get_called_class());
            }

            $this->__root__ = realpath($this->root) . DIRECTORY_SEPARATOR;
        }

        return $this->__root__;
    }

    final public function isComposer()
    {
        return $this->getPackgeType() == 'composer';
    }

    final public function isExtend()
    {
        return $this->getPackgeType() == 'extend';
    }

    final public function copyAssets($force = false)
    {
        if (empty($this->assets)) {
            return true;
        }

        $src = $this->getRoot() . $this->assets . DIRECTORY_SEPARATOR;

        $name = $this->assetsDirName();

        $assetsDir = Tool::checkAssetsDir($name);

        if (!$assetsDir) {

            if (!$force) {
                return true;
            }

            Tool::clearAssetsDir($name);
        }
        $res = Tool::copyDir($src, $assetsDir);

        if ($res) {
            file_put_contents(
                $assetsDir . 'tpext-warning.txt',
                '此目录是存放扩展静态资源的，' . "\n"
                    . '不要替换文件或上传新文件到此目录及子目录，' . "\n"
                    . '否则刷新扩展资源后文件将还原或丢失，' . "\n"
                    . '文件建议传到根目录的`public/static`目录下。'
            );
        }

        ExtLoader::trigger('tpext_copy_assets', $this->getId());

        return $res;
    }

    final public function assetsDirName()
    {
        $name = $this->getName();

        if (empty($name)) {
            $name = get_called_class();
        }

        $name = preg_replace('/\W/', '', $name);

        return $name;
    }

    /**
     * 获取配置文件放置目录
     *
     * @return string
     */
    public function configPath()
    {
        if (!$this->__config_path__) {
            if (is_file($this->getRoot() . 'config.php')) {
                $this->__config_path__ = $this->getRoot() . 'config.php';
            } else {
                //composer包，可能为src目录下
                //建议composer包取消src目录，代码直接放在扩展根目录，可以同时支持composer和extend两种模式
                $this->__config_path__ = $this->getRoot() . 'src' . DIRECTORY_SEPARATOR . 'config.php';
            }
        }

        return $this->__config_path__;
    }

    /**
     * Undocumented function
     *
     * @param boolean $all 是否包含__config__|__saving__两个参数
     * @return array
     */
    final public function defaultConfig($all = false)
    {
        $configPath = $this->configPath();

        if (is_file($configPath)) {

            $config = include $configPath;

            if (!$all) {
                unset($config['__config__'], $config['__saving__']);
            }

            return $config;
        } else {
            return [];
        }
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    final public function getConfig()
    {
        if ($this->__config__ === null) {

            $defaultConfig = $this->defaultConfig();

            $this->__config__ = $defaultConfig;
        }

        return $this->__config__;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @return mixed
     */
    final public static function config($key = null, $default = '')
    {
        $config = static::getInstance()->getConfig();

        if ($key) {
            return isset($config[$key]) ? $config[$key] : $default;
        }

        return $config;
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return array
     */
    final public function setConfig($data = [])
    {
        $this->__config__ = array_merge($this->getConfig(), $data);

        return $this->__config__;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function install()
    {
        $sqlFile = $this->getRoot() . 'data' . DIRECTORY_SEPARATOR . 'install.sql';

        $success = true;

        if (is_file($sqlFile)) {
            $success = Tool::executeSqlFile($sqlFile, $this->errors);
        }

        $this->copyAssets();

        return $success;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function uninstall($runSql = true)
    {
        return true;
    }

    /**
     * 实例被创建以后调用
     *
     * @return $this
     */
    public function created()
    {
        return $this;
    }

    /**
     * 实例安装并启用，查找到之后调用
     *
     * @return $this
     */
    public function loaded()
    {
        return $this;
    }

    abstract public function extInit($info = []);
}
