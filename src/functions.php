<?php namespace Nabeghe;

if (!function_exists('arr_get')) {
    /**
     * @param  array  $array
     * @param $key
     * @param $default
     * @return mixed|null
     */
    function arr_get(array $array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        return $default;
    }
}

if (!function_exists('arr_set')) {
    /**
     * @param  array  $array
     * @param $key
     * @param $value
     * @param  bool  $force
     * @return void
     */
    function arr_set(array &$array, $key, $value, bool $force = true)
    {
        if ($force || !array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
    }
}

if (!function_exists('arr_to_obj')) {
    /**
     * @param  array  $array
     * @param  bool  $deep
     * @return mixed|object
     */
    function arr_to_obj(array $array, bool $deep = true)
    {
        if (!$deep) {
            return (object) $array;
        }

        return @json_decode(json_encode($array), false);
    }
}

if (!function_exists('calc_execution_metrics')) {
    /**
     * @param $callback
     * @return array
     */
    function calc_execution_metrics($callback)
    {
        $time_start = microtime(true);
        $memory_start = memory_get_usage();

        $callback();

        $time_end = microtime(true);
        $memory_end = memory_get_usage();

        return [
            'execution_time' => $time_end - $time_start,
            'memory_usage' => $memory_end - $memory_start,
        ];
    }
}

if (!function_exists('calc_execution_time')) {
    /**
     * @param $callback
     * @return mixed
     */
    function calc_execution_time($callback)
    {
        $time_start = microtime(true);
        $callback();
        $time_end = microtime(true);
        return $time_end - $time_start;
    }
}

if (!function_exists('calc_memory_usage')) {
    /**
     * @param $callback
     * @return int
     */
    function calc_memory_usage($callback)
    {
        $memory_start = memory_get_usage();
        $callback();
        $memory_end = memory_get_usage();
        return $memory_end - $memory_start;
    }
}

if (!function_exists('constant')) {
    /**
     * @param $name1
     * @param $name2
     * @return mixed
     */
    function constant($name1, $name2 = null)
    {
        if ($name2 == '') {
            return \constant($name1);
        }

        return \constant("$name1::$name2");
    }
}

if (!function_exists('define')) {
    /**
     * @param $name
     * @param $value
     * @return bool
     */
    function define($name, $value)
    {
        if (!\defined($name)) {
            \define($name, $value);
            return true;
        }

        return false;
    }
}

if (!function_exists('defined')) {
    /**
     * @param $name1
     * @param $name2
     * @return bool
     */
    function defined($name1, $name2 = null)
    {
        if ($name2 == '') {
            return \defined($name1);
        }

        return \defined("$name1::$name2");
    }
}

if (!function_exists('get_request_execution_time')) {
    /**
     * Get the time elapsed so far during this PHP script.
     * Uses REQUEST_TIME_FLOAT that appeared in PHP 5.4.0.
     *
     * @return float Seconds since the PHP script started.
     * @link https://developer.wordpress.org/reference/functions/timer_float/
     */
    function get_request_execution_time()
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }
}

if (!function_exists('join_paths')) {
    /**
     * Join the given paths together.
     *
     * @param  string|null  $basePath
     * @param  string  ...$paths
     * @return string
     */
    function join_paths($basePath, ...$paths)
    {
        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $basePath.implode('', $paths);
    }
}

if (!function_exists('obj_get')) {
    /**
     * @param  object  $object
     * @param  string  $name
     * @param $default
     * @return mixed|null
     */
    function obj_get(object $object, string $name, $default = null)
    {
        if (property_exists($object, $name)) {
            return $object->$name;
        }

        return $default;
    }
}

if (!function_exists('obj_set')) {
    /**
     * @param  object  $object
     * @param  string  $name
     * @param $value
     * @param  bool  $force
     * @return void
     */
    function obj_set(object $object, string $name, $value, bool $force = true)
    {
        if ($force || !property_exists($object, $name)) {
            $object->$name = $value;
        }
    }
}

if (!function_exists('percent')) {
    /**
     * @param $value
     * @param $total
     * @return float|int
     */
    function percent($value, $total)
    {
        $value = floatval($value);
        $total = floatval($total);

        if (!$value || !$total) {
            return 0;
        }

        return (($value * 100) / $total);
    }
}

if (!function_exists('sanitize_path')) {
    /**
     * @param $path
     * @param  int  $blankcount
     * @return string
     */
    function sanitize_path($path, int &$blankcount = 0): string
    {
        if (empty($path)) {
            return '';
        }

        $sanitized = '';
        $blankcount = 0;

        if (is_string($path)) {
            $path_parts = explode('/', $path);
        } elseif (is_array($path) && count($path) > 0) {
            $path_parts = $path;
        } else {
            return '';
        }

        foreach ($path_parts as $path_part) {
            $path_part = trim($path_part);

            if ($path_part === '') {
                $blankcount++;
            } else {
                $sanitized .= $path_part.'/';
            }
        }

        $sanitized = ltrim($sanitized, '/');
        $sanitized = rtrim($sanitized, '/');

        return $sanitized;
    }
}

if (!function_exists('unpercent')) {
    /**
     * Calculates a percentage of a total value.<br>
     * What is N percent of a total value?
     * @param  float|int  $percent
     * @param  float|int  $total
     * @return float|int
     */
    function unpercent($percent, $total)
    {
        return (($percent * $total) / 100);
    }
}

if (!function_exists('zeroone')) {
    /**
     * @param $value
     * @return int
     */
    function zeroone($value)
    {
        return $value ? 1 : 0;
    }
}