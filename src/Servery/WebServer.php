<?php namespace Nabeghe\Servery;

/**
 * WebServer Checker.
 */
class WebServer
{
    public static function getName(): string
    {
        return $_SERVER['SERVER_NAME'] ?? '';
    }

    public static function isApache(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = (strpos($webserver_name, 'Apache') !== false
                || strpos($webserver_name, 'LiteSpeed') !== false);
        }
        return $is;
    }

    public static function isIIS(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = !static::isApache()
                && (strpos($webserver_name, 'Microsoft-IIS') !== false
                    || strpos($webserver_name, 'ExpressionDevServer') !== false);
        }
        return $is;
    }

    public static function isIIS7(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = static::isIIS()
                && (int) substr($webserver_name, strpos($webserver_name, 'Microsoft-IIS/') + 14) >= 7;
        }
        return $is;
    }

    public static function isLitespeed(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = (strpos($webserver_name, 'litespeed') !== false);
        }
        return $is;
    }

    public static function isNginx(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = (strpos($webserver_name, 'nginx') !== false);
        }
        return $is;
    }

    public static function isPhpStorm(): bool
    {
        static $is;
        if (!isset($is)) {
            $webserver_name = static::getName();
            $is = (strpos($webserver_name, 'PhpStorm') !== false);
        }
        return $is;
    }
}