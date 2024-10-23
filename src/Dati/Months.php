<?php namespace Nabeghe\Dati;

class Months
{
    public static function length(int $year, int $month, $calendar = 'gregorian'): int
    {
        if ($year < 1 || $month < 1 || $month > 12) {
            return 0;
        }

        if ($calendar == 'gregorian') {
            $date = new \DateTime("$year-$month-01");
            return $date->format('t');
        } elseif ($calendar == 'jalali') {
            if ($month <= 6) {
                return 31;
            }
            if ($month <= 11) {
                return 30;
            }
            return Dati::isLeap($year, $calendar === 'jalali') ? 30 : 29;
        }

        return 0;
    }

    public static function name(int $month, string $calendar = 'gregorian', ?bool $english = null)
    {
        if ($calendar == 'gregorian') {
            $english = $english === null ? true : $english;
            switch ($month) {
                case 1:
                    return $english ? 'January' : 'ژانویه';
                case 2:
                    return $english ? 'February' : 'فوریه';
                case 3:
                    return $english ? 'March' : 'مارس';
                case 4:
                    return $english ? 'April' : 'آوریل';
                case 5:
                    return $english ? 'May' : 'مه';
                case 6:
                    return $english ? 'June' : 'ژوئن';
                case 7:
                    return $english ? 'July' : 'ژوئیه';
                case 8:
                    return $english ? 'August' : 'اوت';
                case 9:
                    return $english ? 'September' : 'سپتامبر';
                case 10:
                    return $english ? 'October' : 'اکتبر';
                case 11:
                    return $english ? 'November' : 'نوامبر';
                case 12:
                    return $english ? 'December' : 'دسامبر';
            }
        } elseif ($calendar == 'jalali') {
            $english = $english === null ? false : $english;
            switch ($month) {
                case 1:
                    return $english ? 'Farvardin' : 'فروردین';
                case 2:
                    return $english ? 'Ordibehesht' : 'اردیبهشت';
                case 3:
                    return $english ? 'Khordad' : 'خرداد';
                case 4:
                    return $english ? 'Tir' : 'تیر';
                case 5:
                    return $english ? 'Mordad' : 'مرداد';
                case 6:
                    return $english ? 'Shahrivar' : 'شهریور';
                case 7:
                    return $english ? 'Mehr' : 'مهر';
                case 8:
                    return $english ? 'Aban' : 'آبان';
                case 9:
                    return $english ? 'Azar' : 'آذر';
                case 10:
                    return $english ? 'Dey' : 'دی';
                case 11:
                    return $english ? 'Bahman' : 'بهمن';
                case 12:
                    return $english ? 'Esfand' : 'اسفند';
            }
        } elseif ($calendar == 'lunar') {
            $english = $english === null ? false : $english;
            switch ($month) {
                case 1:
                    return $english ? 'Muharram' : 'محرم';
                case 2:
                    return $english ? 'Safar' : 'صفر';
                case 3:
                    return $english ? 'Rabi\' al-Awwal' : 'ربیع‌الاول';
                case 4:
                    return $english ? 'Rabi\' al-Thani' : 'ربیع‌الثانی';
                case 5:
                    return $english ? 'Jumada al-Awwal' : 'جمادی‌الاول';
                case 6:
                    return $english ? 'Jumada al-Thani' : 'جمادی‌الثانی';
                case 7:
                    return $english ? 'Rajab' : 'رجب';
                case 8:
                    return $english ? 'Sha\'ban' : 'شعبان';
                case 9:
                    return $english ? 'Ramadan' : 'رمضان';
                case 10:
                    return $english ? 'Shawwal' : 'شوال';
                case 11:
                    return $english ? 'Dhu al-Qi\'dah' : 'ذوالقعده';
                case 12:
                    return $english ? 'Dhu al-Hijjah' : 'ذوالحجه';
            }
        }
        return null;
    }

    public static function nameGregorian(int $month, bool $english = true)
    {
        return static::name($month, 'gregorian', $english);
    }

    public static function nameJalali(int $month, bool $english = false)
    {
        return static::name($month, 'jalali', $english);
    }

    public static function nameLunar(int $month, bool $english = false)
    {
        return static::name($month, 'lunar', $english);
    }
}