<?php namespace Nabeghe\Arrayer\Traits;

use Nabeghe\Stringer\Str;

trait NabegheArrTrait
{
    /**
     * Assigns a value to a key in an array.
     *
     * @param  array  $data
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  bool  $force Optional. If false, the new value will not be set if the key already exists. Drfault true.
     */
    public static function assign(&$data, $key, $value, $force = true)
    {
        if ($force || !array_key_exists($key, $data)) {
            $data[$key] = $value;
        }
    }

    /**
     * Converts something into an array.
     * @param  mixed  $value  an array, json, serialized string, ...
     * @return array
     */
    public static function cast($value): array
    {
        if ($value === null) {
            return [];
        }
        if (!is_array($value)) {
            if (is_string($value)) {
                if (Str::startsWith($value, '{')) {
                    $value = @json_decode($value, true);
                } else {
                    $value = @unserialize($value);
                }
            } else {
                try {
                    $value = (array) $value;
                } catch (\Throwable $throwable) {
                }
            }
            if (!is_array($value)) {
                $value = [];
            }
        }
        return $value;
    }

    /**
     * An alaternative for php {@see implode()} function.
     * @param  mixed  $data  Array data; if it's not an array, {@see self::wrap()} is applied to it.
     * @param ?array  $options  Implosion config. Supports the following options:
     *      .@type string $seperator The delimiter for values in the final text, such as a comma.
     *      .@type string $prefix The prefix for the final text that appears at the beginning.
     *      .@type string $suffix The suffix for the final text that appears at the end.
     *      .@type string $item_prefix The prefix that appears at the beginning of each item.
     *      .@type string $item_suffix The suffix that appears at the end of each item.
     *      .@type string $default The default output text returned if the data is empty.
     *      .@type string $callback A callback for filtering values.
     * @return mixed|string
     */
    public static function implode($data, ?array $options = null)
    {
        $data = static::wrap($data);
        $options = static::merge($options ?? [], [
            'seperator' => '',
            'prefix' => '',
            'suffix' => '',
            'item_prefix' => '',
            'item_suffix' => '',
            'default' => '',
        ]);

        $items_count = count($data);
        if ($items_count == 0) {
            return $options['default'];
        }

        $output = '';
        $i = 0;
        foreach ($data as $item) {
            $i++;
            if (isset($options['filter'])) {
                $item = $options['filter']($item);
            }
            if ($item === null || $item === '') {
                continue;
            }
            $output .= $options['item_prefix'].$item.$options['item_suffix'].($i < $items_count ? $options['seperator'] : '');
        }

        $output = $options['prefix'].$output.$options['suffix'];
        return $output;
    }

    /**
     * An alternative for {@see in_array()}, supports multiple needle.<br>
     * All needles must be present in the haystack.
     * @param  array  $haystack
     * @param  iterable|mixed  $needle
     * @param  bool  $strict
     * @return bool
     */
    public static function includes($haystack, $needle, $strict = false)
    {
        if (is_iterable($needle)) {
            foreach ($needle as $n) {
                if (!in_array($n, $haystack, $strict)) {
                    return false;
                }
            }
            return true;
        }
        return in_array($needle, $haystack, $strict);
    }

    /**
     * An alternative for {@see in_array()}, supports multiple needle.<br>
     * At least one of the needles must be present in the haystack.
     * @param  array  $haystack
     * @param $needle
     * @param  bool  $strict
     * @return bool
     */
    public static function includesAny($haystack, $needle, $strict = false)
    {
        if (is_iterable($needle)) {
            foreach ($needle as $n) {
                if (in_array($n, $haystack, $strict)) {
                    return true;
                }
            }
            return false;
        }
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Checks if an array is zero-based or not.
     * @param  array  $array
     * @return bool
     */
    public static function isZeroBasedIndex(array $array)
    {
        return array_values($array) === $array;
        //return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * An alternative for {@see array_merge()}.<br>
     * Uses the {@see self::mergeTwo()} function for all inputs.
     * @param  array  ...$arrays
     * @return array
     */
    public static function merge(...$arrays)
    {
        $result = array_shift($arrays);
        foreach ($arrays as $array) {
            $result = static::mergeTwo($result, $array);
        }
        return $result;
    }

    /**
     * It merges the second array into the first array.
     * - If the second array is zero-based, all its items are added to the first array.
     * - If the second array is not zero-based, for each item, if the key doesn't exist in the first array,
     *   its value is added to the first array with the same key.
     *   However, if the key exists and both values are arrays,
     *   the current function is called again with the two new arrays, effectively merging nested arrays as well!
     * @param  array  $arr1
     * @param  array  $arr2
     * @return array merged array.
     */
    public static function mergeTwo($arr1, $arr2)
    {
        if (static::isZeroBasedIndex($arr2)) {
            foreach ($arr2 as $value) {
                $arr1[] = $value;
            }
        } else {
            foreach ($arr2 as $key => $value) {
                if (!array_key_exists($key, $arr1)) {
                    $arr1[$key] = $value;
                } elseif (is_array($value) && is_array($arr1[$key])) {
                    $arr1[$key] = static::mergeTwo($arr1[$key], $value);
                }
            }
        }
        return $arr1;
    }

    /**
     * Removes one or more items from an array.
     * @param  array  $data  The array.
     * @param  mixed  $values  Value or array of values to be removed
     * @return bool True if an item was removed, false otherwise.
     */
    public static function remove(&$data, $values)
    {
        $new_arr = [];
        $length = count($data);
        $values = static::wrapEasy($values);
        $remvoed = false;
        for ($i = 0; $i < $length; $i++) {
            if (!in_array($data[$i], $values)) {
                $new_arr[] = $data[$i];
            } else {
                $remvoed = true;
            }
        }
        $data = $new_arr;
        return $remvoed;
    }

    /**
     * Wraps anything other than an array in a new array and returns it.
     * @param $data
     * @return array
     */
    public static function wrapEasy($data)
    {
        return is_array($data) ? $data : [$data];
    }

    /**
     * Wraps anything in a new array and returns it.
     * @param $data
     * @return array
     */
    public static function wrapForce($data)
    {
        return [$data];
    }
}