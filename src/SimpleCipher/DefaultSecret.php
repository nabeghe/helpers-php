<?php namespace Nabeghe\SimpleCipher;

class DefaultSecret
{
    public static function path()
    {
        return __DIR__.'/../'.md5(__FILE__).'.secret.php';
    }

    public static function get()
    {
        if (defined('SIMPLE_CIPHER_SECRET')) {
            return constant('SIMPLE_CIPHER_SECRET');
        } elseif (isset($_ENV['SIMPLE_CIPHER_SECRET'])) {
            return $_ENV['SIMPLE_CIPHER_SECRET'];
        }

        $path = static::path();
        if (file_exists($path)) {
            $secret = include $path;
            if (empty($secret) || !is_string($secret)) {
                $secret = null;
            }
        }

        if (empty($secret)) {
            $secret = static::random();
            if (!file_put_contents($path, "<?"."php return '$secret';")) {
                return '';
            }
        }

        return $secret;
    }

    public static function reest()
    {
        $path = static::path();

        if (file_exists($path)) {
            unlink($path);
        }

        return !file_exists($path);
    }

    public static function random()
    {
        return bin2hex(random_bytes(16));
        //$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        //$length = strlen($chars);
        //$secret = '';
        //for ($i = 0; $i < 32; $i++) {
        //    $secret .= $chars[mt_rand(0, $length - 1)];
        //}
        //return $secret;
    }
}