<?php

namespace tpext\think;

class App
{
    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getAppPath()
    {
        return app_path() . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getConfigPath()
    {
        return config_path() . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getExtendPath()
    {
        return base_path() . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getPublicPath()
    {
        return public_path() . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getRuntimePath()
    {
        return runtime_path() . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getRootPath()
    {
        return base_path() . DIRECTORY_SEPARATOR;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getDefaultLang()
    {
        return config('lang.default_lang', 'zh-cn');
    }
}
