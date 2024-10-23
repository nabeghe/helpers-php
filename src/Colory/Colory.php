<?php namespace Nabeghe\Colory;

class Colory
{
    public static function normalizeRgb($rgb)
    {
        if (is_array($rgb) && array_values($rgb) === $rgb && count($rgb) >= 3) {
            $rgb = ['r' => $rgb[0], 'g' => $rgb[1], 'b' => $rgb[2], 'a' => $rgb[3] ?? null];
            if ($rgb[3] === null) {
                unset($rgb[3]);
            }
        }

        if (!is_array($rgb) || !isset($rgb['r']) || !isset($rgb['g']) || !isset($rgb['b'])
            || !is_numeric($rgb['r']) || !is_numeric($rgb['g']) || !is_numeric($rgb['b'])
            || (isset($rgb['a']) && !is_numeric($rgb['a']))) {
            return null;
        }

        $rgb['r'] = max(0, min(255, $rgb['r']));
        $rgb['g'] = max(0, min(255, $rgb['g']));
        $rgb['b'] = max(0, min(255, $rgb['b']));
        if (isset($rgb['a'])) {
            $rgb['a'] = max(0, min(1, $rgb['a']));
            $rgb['a'] = (float) number_format($rgb['a'], 2);
        }

        return $rgb;
    }

    public static function randomRgb($alpha = false)
    {
        $color = [
            'r' => rand(0, 255), // red
            'g' => rand(0, 255), // green
            'b' => rand(0, 255), // blue
        ];

        if ($alpha) {
            $color['a'] = rand(0, 100) / 100;
        }

        return $color;
    }

    public static function randomRgbCss($alpha = false)
    {
        $color = static::randomRgb($alpha);

        if ($alpha) {
            return "rgba($color[r], $color[g], $color[b], $color[a])";
        }

        return "rgba($color[r], $color[g], $color[b])";
    }

    public static function randomHex($alpha = false, $hash = true)
    {
        $color = sprintf("%06x", rand(0, 0xFFFFFF));

        if ($hash) {
            $color = '#'.$color;
        }

        if ($alpha) {
            $color .= dechex(rand(0, 255));
        }

        $color = strtoupper($color);
        return $color;

        //$rand = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
        //$color = ($hash ? '#' : '')
        //    .$rand[rand(0, 15)].$rand[rand(0, 15)]
        //    .$rand[rand(0, 15)].$rand[rand(0, 15)]
        //    .$rand[rand(0, 15)].$rand[rand(0, 15)];
        //if ($alpha) {
        //    $color .= dechex(rand(0, 255));
        //}
        //return $color;
    }

    public static function invertRgb($rgb, $alpha = 1)
    {
        $rgb = static::normalizeRgb($rgb);

        if (!$rgb) {
            return null;
        }

        $rgb['r'] = 255 - $rgb['r'];
        $rgb['g'] = 255 - $rgb['r'];
        $rgb['b'] = 255 - $rgb['r'];

        if (isset($rgb['a'])) {
            $rgb['a'] = 1 - $rgb['a'];
        }

        return $rgb;
    }

    public static function invertHex($color)
    {
        $color = trim($color);
        $prepended_hash = false;

        if ('' === '#' || false !== strpos($color, '#')) {
            $prepended_hash = true;
            $color = str_replace('#', '', $color);
        }

        $len = strlen($color);

        if ($len == 3 || $len == 6 || $len == 8) {
            if ($len == 3) {
                $color = preg_replace('/(.)(.)(.)/', "\\1\\1\\2\\2\\3\\3", $color);
            } elseif ($len == 8) {
                $alpha = substr($color, 6, 2);
                $color = substr($color, 0, 6);
            }
        } else {
            return null;
        }

        if (!preg_match('/^[a-f0-9]{6}$/i', $color)) {
            return null;
        }

        $r = dechex(255 - hexdec(substr($color, 0, 2)));
        $r = (strlen($r) > 1) ? $r : '0'.$r;

        $g = dechex(255 - hexdec(substr($color, 2, 2)));
        $g = (strlen($g) > 1) ? $g : '0'.$g;

        $b = dechex(255 - hexdec(substr($color, 4, 2)));
        $b = (strlen($b) > 1) ? $b : '0'.$b;

        return strtoupper(($prepended_hash ? '#' : '').$r.$g.$b.(isset($alpha) ? $alpha : ''));
    }

    public static function rgbToHex($rgb, $alpha = true)
    {
        $rgb = static::normalizeRgb($rgb);

        if (!$rgb) {
            return null;
        }

        $hex = sprintf(
            "#%02x%02x%02x",
            max(0, min(255, $rgb['r'])),
            max(0, min(255, $rgb['g'])),
            max(0, min(255, $rgb['b'])),
        );

        if ($alpha && isset($rgb['a'])) {
            $hex .= sprintf("%02x", floor($rgb['a'] * 255));
        }

        return strtoupper($hex);
    }

    public static function hexToRgb($hex, $alpha = true)
    {
        $hex = ltrim($hex, '#');
        $length = strlen($hex);

        // normalize
        if ($length === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if ($length < 6 || $length == 7 || $length > 8) {
            return null;
        }

        $color = [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];

        if ($alpha) {
            $color['a'] = $length === 8 ? hexdec(substr($hex, 6, 2)) / 255 : 1;
        }

        $color = static::normalizeRgb($color);
        return $color;
    }
}