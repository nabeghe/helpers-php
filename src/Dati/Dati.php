<?php namespace Nabeghe\Dati;

use DateTime;
use DateTimeZone;

class Dati
{
    public const FORMAT = 'Y-m-d H:i:s';

    /**
     * Validate a Gregorian or Jalali date.
     *
     * @param  int  $month
     * @param  int  $day
     * @param  int  $year
     * @param  bool  $isJalai  Optional. Default false.
     * @return bool
     */
    public static function checkDate($month, $day, $year, $isJalai = false)
    {
        if ($isJalai) {
            $l_d = ($month == 12 and ((($year + 12) % 33) % 4) != 1) ? 29 : (31 - (int) ($month / 6.5));
            return !(($month > 12 or $day > $l_d or $month < 1 or $day < 1 or $year < 1));
        }

        return checkdate($month, $day, $year);
    }

    /**
     * Checks if a sequence of datetimes is spam.
     *
     * @param  string[]  $datetimes
     * @param  int  $offset  Optional. How many seconds of difference between two consecutive dates should be considered a warning (flag)? Default 1.
     * @param  int  $limit  Optional. What number of maximum warnings, if exceeded, will be considered spam? Default 1.
     * @param  bool  $strict  Optional. If not strict, when the difference between two datetimes doesn't reach the warning threshold, one warning will be deducted. Default false.
     * @return bool
     */
    public static function checkSpam($datetimes, $offset = 1, $limit = 3, $strict = false)
    {
        if (!is_array($datetimes) || ($datetimes_count = count($datetimes)) < 2 || $datetimes_count < $limit || $limit <= 0) {
            return false;
        }

        $warnings_count = 0;
        for ($i = 1; $i < $datetimes_count; $i++) {
            $datetime = $datetimes[$i];
            $prev_datetime = $datetimes[$i - 1];
            if (static::diff($datetime, $prev_datetime, 'seconds') <= $offset) {
                $warnings_count++;
            } elseif (!$strict) {
                $warnings_count--;
                if ($warnings_count <= 0) {
                    $warnings_count = 0;
                }
            }
        }

        return ($warnings_count >= $limit);
    }

    /**
     * Detects the format of the given datetime.
     *
     * @param  string  $datetime
     * @return string|null
     */
    public static function detectFormat($datetime)
    {
        $formats = [
            'Y-m-d H:i:s.u',      // 1995-11-20 00:00:00.000000
            'Y-m-d H:i:s',        // 1995-11-20 00:00:00
            'Y-m-d H:i',          // 1995-11-20 00:00
            'Y-m-d\TH:i:sP',      // 1995-11-20T00:00:00+00:00 (ISO 8601)
            'Y-m-d\TH:i:s.uP',    // 1995-11-20T00:00:00.000000+00:00
            'Y-m-d\TH:i:s\Z',     // 1995-11-20T00:00:00Z (UTC)
            'Y-m-d',              // 1995-11-20
            'd/m/Y H:i:s',        // 20/11/1995 00:00:00
            'd/m/Y H:i',          // 20/11/1995 00:00
            'd/m/Y',              // 20/11/1995
            'm/d/Y H:i:s',        // 11/20/1995 00:00:00
            'm/d/Y H:i',          // 11/20/1995 00:00
            'm/d/Y',              // 11/20/1995
            'd-m-Y H:i:s',        // 20-11-1995 00:00:00
            'd-m-Y H:i',          // 20-11-1995 00:00
            'd-m-Y',              // 20-11-1995
            'm-d-Y H:i:s',        // 11-20-1995 00:00:00
            'm-d-Y H:i',          // 11-20-1995 00:00
            'm-d-Y',              // 11-20-1995
            'Y/m/d',              // 1995/11/20
            'd.m.Y H:i:s',        // 20.11.1995 00:00:00
            'd.m.Y H:i',          // 20.11.1995 00:00
            'd.m.Y',              // 20.11.1995
            'H:i:s',              // 00:00:00
            'H:i',                // 00:00
            'g:i A',              // 12:00 AM
            'g:i:s A',            // 12:00:00 AM
        ];

        foreach ($formats as $format) {
            $dateTimeObj = DateTime::createFromFormat($format, $datetime);
            if ($dateTimeObj && $dateTimeObj->format($format) === $datetime) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Calculates the difference between two datetimes in various units.
     *
     * @param  string  $datetime1  The first date.
     * @param  string  $datetime2  The second date.
     * @param  string  $unit  Optional. The unit in which to return the difference. Default 'days'.
     * @return float The difference between the two dates in the specified unit.
     */
    public static function diff($datetime1, $datetime2, $unit = 'seconds')
    {
        $time_interval = abs(strtotime($datetime2) - strtotime($datetime1));

        switch ($unit) {
            case 'y':
            case 'year':
            case 'years':
                $time_interval = $time_interval / 60 / 60 / 24 / 365; // sec => min => hour => day => year
                break;
            case 'M':
            case 'month':
            case 'months':
                $time_interval = $time_interval / 60 / 60 / 24 / 30; // sec => min => hour => day => month
                break;
            case 'w':
            case 'week':
            case 'weeks':
                $time_interval = $time_interval / 60 / 60 / 24 / 7; // sec => min => hour => day => week
                break;
            case 'd':
            case 'day':
            case 'days':
                $time_interval = $time_interval / 60 / 60 / 24; // sec => min => hour => day
                break;
            case 'h':
            case 'hour':
            case 'hours':
                $time_interval = $time_interval / 60 / 60; // sec => min => hour
                break;
            case 'm':
            case 'min':
            case 'minute':
            case 'minutes':
                $time_interval = $time_interval / 60; // sec => min
                break;
        }

        return (float) number_format((float) $time_interval, 2, '.', '');
    }

    /**
     * @param $datetime1
     * @param $datetime2
     * @return array|null
     */
    public static function diffDetails($datetime1, $datetime2)
    {
        try {
            $dt1 = is_string($datetime1) ? new DateTime($datetime1) : $datetime1;
            $dt2 = is_string($datetime2) ? new DateTime($datetime2) : $datetime2;
            $diff = $dt1->diff($dt2);
            $details = array_intersect_key((array) $diff, array_flip(['y', 'm', 'd', 'h', 'i', 's']));
            return $details;
        } catch (\Throwable $throwable) {
        }

        return null;
    }

    /**
     * Calculates the difference between two datetimes based on the largest possible unit.
     *
     * @param  string  $datetime1
     * @param  string  $datetime2
     * @return array "Includes three keys:
     *  unit, value, and diff,
     *  representing the largest possible unit,
     *  the difference based on the largest possible unit, and the difference in seconds, respectively.
     */
    public static function howLongAgo($datetime1, $datetime2)
    {
        $diff = (int) self::diff($datetime1, $datetime2, 'seconds');

        if ($diff >= 31536000) {
            $unit = 'years';
            $value = (int) ($diff / 60 / 60 / 24 / 365);
        } elseif ($diff >= 604800) {
            $unit = 'weeks';
            $value = (int) ($diff / 60 / 60 / 24 / 7);
        } elseif ($diff >= 86400) {
            $unit = 'days';
            $value = (int) ($diff / 60 / 60 / 24);
        } elseif ($diff >= 3600) {
            $unit = 'hours';
            $value = (int) ($diff / 60 / 60);
        } elseif ($diff >= 60) {
            $unit = 'minutes';
            $value = (int) ($diff / 60);
        } else {
            $unit = 'seconds';
            $value = $diff;
        }

        return ['unit' => $unit, 'value' => $value, 'diff' => $diff];
    }

    /**
     * Checks if the given year is a leap year or not.
     *
     * @param  int  $year  The year to be checked.
     * @param  bool  $jalali  Is persian calendar?
     * @return bool True if the given year is a leap year, otherwise false.
     */
    public static function isLeap($year, $jalali = false): bool
    {
        return $jalali ? in_array($year % 33, [1, 5, 9, 13, 17, 22, 26, 30])
            : ($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0);
    }

    /**
     * Adds/Subtracts a specified value to/from a datetime.
     *
     * @param  string  $value  Optional. The value to be joined. Default +1.
     * @param  string  $unit  Optional The unit of time for the value. Default 'days'.
     * @param  string|null  $datetime  Optional. The datetime to which the value and unit should be joined. Default current.
     * @return string Returns the joined datetime.
     */
    public static function join($value = '+1', $unit = 'days', $datetime = null)
    {
        $value = self::resolveJoinableValue($value);

        switch ($unit) {
            case 's':
            case 'second':
            case 'seconds':
                $unit = 'second';
                break;
            case 'm':
            case 'minute':
            case 'minutes':
                $unit = 'minutes';
                break;
            case 'h':
            case 'hour':
            case 'hours':
                $unit = 'hour';
                break;
            case 'd':
            case 'day':
            case 'days':
                $unit = 'days';
                break;
        }

        if ($datetime === null) {
            $datetime = Now::datetimeLocal();
        }

        return date(static::FORMAT, strtotime("$value $unit", strtotime($datetime)));
    }

    public static function remaining($datetime1, $datetime2, $validity = 1.0, $unit = 'seconds')
    {
        $validity = abs($validity);

        $elasped = static::diff($datetime1, $datetime2, $unit);
        $remaining = $validity - $elasped;
        if ($remaining <= 0) {
            return 0;
        }

        return (float) number_format((float) $elasped, 2, '.', '');
    }

    protected static function resolveJoinableValue($val)
    {
        $startsWith = function ($haystack, $needle) {
            return 0 === strncmp($haystack, $needle, strlen($needle));
        };

        $val = (int) explode('.', "$val")[0];
        if (!$startsWith($val, '+') && !$startsWith($val, '-')) {
            $val = "+$val";
        }

        return $val;
    }

    /**
     * Converts a datetime from one timezone to another.
     *
     * @param  string  $datetime
     * @param  string  $from
     * @param  string  $to
     * @param  ?string  $format
     * @return ?string
     */
    public static function toTimeZone($datetime, $to = null, $from = 'GMT', $format = null)
    {
        try {
            $dt = new DateTime($datetime, new DateTimeZone($from));
            $dt->setTimezone(new DateTimeZone($to ?: date_default_timezone_get()));
            return $dt->format($format ?: static::FORMAT);
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Converts a datetime in GMT timezone to the local datetime.
     *
     * @param  string  $datetime
     * @param  string|null  $format
     * @return string|null
     */
    public static function toLocal($datetime, $format = null)
    {
        return static::toTimeZone($datetime, 'GMT', date_default_timezone_get(), $format);
    }
}