<?php namespace Nabeghe\Dati;

class Now
{
    public const FORMAT = 'Y-m-d H:i:s';

    protected static ?string $datetime = null;

    protected static ?string $datetimeLocal = null;

    protected static $generator = null;

    public static function init(?string $datetime = null, ?string $datetimeLocal = null, $generator = null): void
    {
        static::$datetime = $datetime;
        static::$datetimeLocal = $datetimeLocal;
        static::$generator = $generator;
        static::datetime();
        static::datetimeLocal();
    }

    public static function datetime(): string
    {
        if (static::$datetime === null) {
            static::$datetime = gmdate(static::FORMAT);
        }

        return static::$datetime;
    }

    public static function datetimeNew(): string
    {
        return gmdate(static::FORMAT);
    }

    public static function datetimeLocal(): string
    {
        if (static::$datetimeLocal === null) {
            static::$datetimeLocal = static::datetimeLocalNew(static::FORMAT);
        }

        return static::$datetimeLocal;
    }

    public static function datetimeLocalNew(): string
    {
        if ($generator = static::$generator) {
            return $generator(static::FORMAT);
        }

        return date(static::FORMAT);
    }
}