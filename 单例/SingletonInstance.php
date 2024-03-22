<?php


namespace App\Helpers;

/**
 * 单例模式 不允许继承
 *
 * Class SingletonInstance
 * @package App\Helpers
 */
final class SingletonInstance
{
    /**
     * 单例容器
     * @var array
     */
    private static $instance=[];

    /**
     * 单例构造
     *
     * @param \stdClass $instance
     * @return mixed|\stdClass
     */
    public static function getInstance($instance)
    {
        return self::$instance[$instance] ?? new $instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}