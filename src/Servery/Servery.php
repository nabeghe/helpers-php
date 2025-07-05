<?php namespace Nabeghe\Servery;

class Servery
{
    /**
     * Localhost names and IPs.
     */
    const LOCAL_HOSTS = ['localhost', '127.0.0.1', '::1'];

    /**
     * Checks if the current environment is running locally.
     *
     * @return bool
     */
    public static function isLocalHost(): bool
    {
        static $result;
        if (isset($result)) {
            return $result;
        }

        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? null;

        if (empty($remote_addr) || in_array($remote_addr, static::LOCAL_HOSTS, true)) {
            return $result = true;
        }

        $server_name = $_SERVER['SERVER_NAME'] ?? '';
        $http_host = $_SERVER['HTTP_HOST'] ?? '';

        if (in_array(strtolower($server_name), static::LOCAL_HOSTS, true) || in_array(strtolower($http_host), static::LOCAL_HOSTS, true)) {
            return $result = true;
        }

        if (filter_var($remote_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $remote_addr);

            if ($parts[0] === '10') {
                return $result = true;
            }

            if ($parts[0] === '172' && $parts[1] >= 16 && $parts[1] <= 31) {
                return $result = true;
            }

            if ($parts[0] === '192' && $parts[1] === '168') {
                return $result = true;
            }
        }

        if (strpos($remote_addr, '::1') === 0) {
            return $result = true;
        }

        return $result = false;
    }

    /**
     * @return string
     */
    public static function getWebServer(): string
    {
        if (!empty($_SERVER['SERVER_NAME']) && is_string($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return 'localhost';
    }

    /**
     * Returns the Server's IP address.
     *
     * @return string
     */
    public static function getServerIP(): string
    {
        return $_SERVER['SERVER_ADDR'] ?? '';
    }

    /**
     * Returns the client's IP address, using a variety of methods to ensure accuracy.
     *
     * @return string The client's IP address.
     */
    public static function getClientIP(): string
    {
        static $ip;
        if (isset($ip)) {
            return $ip;
        }

        $ip = '';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } elseif (!empty($_ENV['REMOTE_ADDR'])) {
                $ip = $_ENV['REMOTE_ADDR'];
            }

            $entries = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            //$entries = preg_split( '[, ]', $_SERVER['HTTP_X_FORWARDED_FOR'] );

            foreach ($entries as $entry) {
                $entry = trim($entry);
                if (preg_match("/^(\d+\.\d+\.\d+\.\d+)/", $entry, $ip_list)) {
                    $private_ip = [
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2\d)|(3[0-1]))\..*/',
                        '/^10\..*/',
                    ];
                    $found_ip = preg_replace($private_ip, $ip, $ip_list[1]);
                    if ($ip != $found_ip) {
                        $ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } elseif (!empty($_ENV['REMOTE_ADDR'])) {
                $ip = $_ENV['REMOTE_ADDR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED'];
            } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
                $ip = $_SERVER['HTTP_FORWARDED'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        if (is_array($ip)) {
            $ip = current($ip);
        }
        if (!is_string($ip)) {
            $ip = '';
        }

        return $ip;
    }

    /**
     * Returns the host address without "www", for example, elatel.ir.
     *
     * @return string
     */
    public static function getHostAddress(): string
    {
        static $host;

        if (!isset($host)) {
            $possible_host_sources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];

            $source_transformations = [
                "HTTP_X_FORWARDED_HOST" => function ($value) {
                    $elements = explode(',', $value);

                    return trim(end($elements));
                },
            ];

            $host = '';
            foreach ($possible_host_sources as $source) {
                if (!empty($host)) {
                    break;
                }
                if (empty($_SERVER[$source])) {
                    continue;
                }
                $host = $_SERVER[$source];
                if (array_key_exists($source, $source_transformations)) {
                    $host = $source_transformations[$source]($host);
                }
            }

            $host = preg_replace('/:\d+$/', '', $host);
            $host = str_ireplace('www.', '', $host);
            $host = trim($host);
        }

        return $host;
    }

    /**
     * An alias for {@see static::getHostAddress()}.
     *
     * @return string
     */
    public static function getDomain(): string
    {
        return static::getHostAddress();
    }

    /**
     * Checks whether the server supports HTTPS or not.
     *
     * @return bool
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443);
    }

    /**
     * Returns the port used in the link, which is either HTTP or HTTPS.
     *
     * @return string
     */
    public static function getUrlProtocol(): string
    {
        if (static::isHttps()) {
            return 'https';
        }

        return 'http';
    }

    /**
     * Returns the HTTP protocol used by the server.
     *
     * @return string
     */
    public static function getServerProtocol(): string
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';

        if (!in_array($protocol, ['HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3'], true)) {
            $protocol = 'HTTP/1.0';
        }

        return $protocol;
    }

    /**
     * Returns the scheme used in the URL, which is either `http://` or `https://`.
     *
     * @return string
     */
    public static function getUrlScheme(): string
    {
        return static::getUrlProtocol().'://';
    }

    /**
     * Returns the URL of the root. The root is a combination of the protocol & the host address.
     * for example: https://elatel.ir
     *
     * @return string
     */
    public static function getRootUrl(): string
    {
        return static::getUrlScheme().static::getHostAddress();
    }

    /**
     * Returns the current requested URL.
     *
     * @return string
     */
    public static function getCurrentUrl(): string
    {
        return static::getRootUrl().(isset($_SERVER['REQUEST_URI']) ? dirname($_SERVER['REQUEST_URI']) : '');
    }

    /**
     * An alternative for the {@see static::getCurrentUrl()}.
     *
     * @return string
     */
    public static function getRequestedUrl(): string
    {
        return static::getCurrentUrl();
    }


    /**
     * Returns the requested path, using `$_SERVER['SCRIPT_FILENAME']`.
     *
     * @return string|null
     */
    public static function getRequrestedPath(): ?string
    {
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $path = $_SERVER['SCRIPT_FILENAME'];
            $path = dirname($path);
            $path = rtrim($path, '/');

            return $path;
        }

        return null;
    }

    /**
     * Returns the project home path.
     *
     * @return string
     */
    public static function getHomePath(): string
    {
        $path = static::getRequrestedPath();

        if ($path === null) {
            $path = __DIR__.'/../../../..'; // from vendor/nabeghe/servery/src
        }

        return $path;
    }

    /**
     * Returns the project home URL.
     *
     * @return string|null
     */
    public static function getHomeUrl(): ?string
    {
        $path = static::getRequrestedPath();

        if ($path === null) {
            return null;
        }

        $home_url = static::getRootUrl().($path ? "/$path" : '');

        return $home_url;
    }

    /**
     * An alias for {@see UserAgent::$instance()}.
     *
     * @return UserAgent
     */
    public static function getUserAgent(): UserAgent
    {
        return UserAgent::instance();
    }

    /**
     * An alias for {@see UserAgent::getCurrentReal()}.
     *
     * @return string
     */
    public static function getUserAgentValue(): string
    {
        return UserAgent::getCurrentReal();
    }
}