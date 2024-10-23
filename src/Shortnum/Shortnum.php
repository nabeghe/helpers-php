<?php namespace Nabeghe\Shortnum;

/**
 * Number shortener.
 */
class Shortnum
{
    /**
     * Supported units.
     */
    public const UNITS = [
        '', 'K', 'M', 'B', 'T', 'Qa', 'Qi', 'Sx', 'Sp', 'Oc', 'No', 'Dc', 'Ud', 'Dd', 'Td', 'Qa', 'Qi', 'Sx', 'Sp',
        'Oc', 'No',
    ];

    /**
     * Shortens a number.
     *
     * @param int $number
     * @return string
     */
    public static function short($number)
    {
        if ($number < 1000) {
            return "$number";
        }

        $units_count = count(static::UNITS);
        $unit_index = 0;
        while ($number >= 1000 && $unit_index < $units_count - 1) {
            $number /= 1000;
            $unit_index++;
        }

        $formatted_number = number_format($number, 2);
        if (substr($formatted_number, -3) === '.00') {
            $formatted_number = substr($formatted_number, 0, -3);
        } elseif (substr($formatted_number, -1) === '0') {
            $formatted_number = substr($formatted_number, 0, -1);
        }

        return $formatted_number.static::UNITS[$unit_index];
    }
}