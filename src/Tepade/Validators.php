<?php namespace Nabeghe\Tepade;

class Validators
{
    public const ALPHABETIC = '[a-zA-Z]+';
    public const ALPHABETIC_NUMERIC = '[a-zA-Z0-9]+';
    public const NUMERIC = '[0-9]+';

    public static function in(array $values): string
    {
        $values = array_map(function ($item) {
            return (string) $item;
        }, $values);
        return implode('|', $values);
    }
}