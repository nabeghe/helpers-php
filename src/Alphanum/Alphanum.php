<?php namespace Nabeghe\Alphanum;

/**
 * A way to converting numbers between decimal & alphanumeric formats & vice versa.
 */
class Alphanum
{
    public const CHARS = '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';

    /**
     * @param  int  $number  Non-negative decimal number.
     * @return ?string
     */
    public static function generate($number)
    {
        if ($number < 0) {
            return null;
        }
        if ($number === 0) {
            return '0';
        }

        $result = '';
        while ($number > 0) {
            $result = static::CHARS[$number % 60].$result;
            $number = intdiv($number, 60);
        }

        return $result;
    }

    /**
     * @param  string  $alphanum  sexagesimal number to convert
     * @return integer Decimal representation of sexagesimal number
     */
    public static function toDecimal(string $alphanum): ?int
    {
        $length = strlen($alphanum);
        $result = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = $alphanum[$i];
            $value = strpos(static::CHARS, $char);
            if ($value === false) {
                return null;
            }
            $result = $result * 60 + $value;
        }

        return $result;
    }
}