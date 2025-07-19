<?php namespace Nabeghe\FileSize;

use InvalidArgumentException;

/**
 * Helper methods related to file size operations.
 */
class FileSize
{
    /**
     * Returns all supported units.
     *
     * - bits: ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb']
     * - bytes: ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
     *
     * @param  bool  $bits  Return bit units instead of byte units.
     * @return array
     */
    public static function getAllUnits(bool $bits = false): array
    {
        return $bits
            // bit, kilobit, megabit, gigabit, terabit, petabit, exabit, zettabit, yottabit
            ? ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb']
            // byte, kilobyte, megabyte, gigabyte, terabyte, petabyte, exabyte, zettabyte, yottabyte
            : ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    }

    /**
     * Checks whether a unit is valid or not.
     *
     * @param  string  $unit  Unit value.
     * @return bool
     */
    public static function isValidUnit(string $unit): bool
    {
        $units = [
            static::getAllUnits(),
            static::getAllUnits(true),
        ];

        $normalized_unit = ucfirst(strtolower($unit));

        if (strtolower($unit) === 'b') {
            $normalized_unit = 'b';
        }

        if (strtoupper($unit) === 'B' && strlen($unit) === 1) {
            $normalized_unit = 'B';
        }

        return in_array($normalized_unit, $units, true);
    }

    /**
     * Format a number with optional decimals, Returns integer if no decimals needed.
     *
     * @param  float|int  $size  Number to format.
     * @param  int  $decimals  Optional. Decimal places. Default 2.
     * @return int|float Formatted number as int or float.
     */
    public static function format($size, int $decimals = 2)
    {
        if (is_int($size)) {
            return $size;
        }

        $rounded = round($size, $decimals);
        $is_whole = ($rounded == (int) $rounded);

        return $is_whole ? (int) $rounded : $rounded;
    }

    /**
     * Converts a data size from one unit to another (supports bits and bytes).
     *
     * @param  float|int  $size  The numeric value to convert.
     * @param  string  $fromUnit  Source unit (e.g. "MB", "Gb", "B", "Kb").
     * @param  string  $toUnit  Target unit to convert to.
     * @param  bool|int  $format  Optional. Round and format result to given decimal places (true = 2 decimals). Default false.
     * @param  bool|int  $isBinary  Optional. Whether to use binary (1024) or decimal (1000) base. Default true.
     * @return float|int Converted value (raw or formatted).
     * @throws InvalidArgumentException If either unit is invalid.
     */
    public static function convert($size, string $fromUnit, string $toUnit, $format = false, $isBinary = true)
    {
        $units = [
            'b' => -1,
            'B' => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8,
            'Kb' => 1,
            'Mb' => 2,
            'Gb' => 3,
            'Tb' => 4,
            'Pb' => 5,
            'Eb' => 6,
            'Zb' => 7,
            'Yb' => 8,
        ];

        if (!isset($units[$fromUnit])) {
            throw new InvalidArgumentException("Invalid fromUnit: $fromUnit");
        }

        if (!isset($units[$toUnit])) {
            throw new InvalidArgumentException("Invalid toUnit: $toUnit");
        }

        $is_bit = function ($unit) {
            $len = strlen($unit);
            return $len > 0 && substr($unit, -1) === 'b' && $unit !== 'B';
        };

        $is_from_bit = $is_bit($fromUnit);
        $is_to_bit = $is_bit($toUnit);

        // Get index values (powers of 1024)
        $from_index = $units[$fromUnit];
        $to_index = $units[$toUnit];

        // Convert to bytes
        $bytes = $size;
        if ($is_from_bit) {
            $bytes = $size / 8; // Convert bits to bytes
        }
        if ($from_index > 0) {
            $bytes *= pow($isBinary ? 1024 : 1000, $from_index); // Convert from fromUnit to base bytes
        }

        // Convert from bytes to target unit
        $result = $bytes;
        if ($to_index > 0) {
            $result /= pow($isBinary ? 1024 : 1000, $to_index); // Convert to toUnit
        }
        if ($is_to_bit) {
            $result *= 8; // Convert bytes to bits
        }

        if ($format) {
            $result = static::format($result, $format === true ? 2 : $format);
        }

        return $result;
    }

    /**
     * Compares two data sizes across different units.
     *
     * @param  float|int  $size1  First value to compare.
     * @param  string  $unit1  Unit of the first value (e.g. "MB", "Gb").
     * @param  float|int  $size2  Second value to compare.
     * @param  string  $unit2  Unit of the second value.
     * @return int Returns -1 if size1 < size2, 1 if size1 > size2, or 0 if equal.
     */
    public static function compare($size1, string $unit1, $size2, string $unit2): int
    {
        // Convert both values to bytes using convert method
        $bytes1 = static::convert($size1, $unit1, 'B');
        $bytes2 = static::convert($size2, $unit2, 'B');

        if ($bytes1 < $bytes2) {
            return -1;
        } elseif ($bytes1 > $bytes2) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Detects the most suitable unit for a given size (bytes or bits).
     *
     * @param  float|int  $size  The size to evaluate.
     * @param  bool  $isBits  Whether the size is in bits (default: false for bytes).
     * @param  float|null  $finalSize  Outputs the normalized size after unit scaling.
     * @param  bool|int  $isBinary  Optional. Whether to use binary (1024) or decimal (1000) base. Default true.
     * @return string The best-fit unit (e.g. "MB", "Gb").
     */
    public static function detectUnit($size, bool $isBits = false, ?float &$finalSize = null, bool $isBinary = true): string
    {
        $units = $isBits ? ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'] : ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $divider = $isBinary ? 1024 : 1000;
        $unitIndex = 0;

        // Normalize up (e.g. 2048 -> 2 KB)
        while ($size >= $divider && $unitIndex < count($units) - 1) {
            $size /= $divider;
            $unitIndex++;
        }

        // Normalize down (e.g. 0.0005 GB -> 0.5 MB)
        while ($size < 1 && $unitIndex > 0) {
            $size *= $divider;
            $unitIndex--;
        }

        $finalSize = $size;

        return $units[$unitIndex];
    }

    /**
     * Converts a size to a human-readable string with appropriate unit.
     *
     * @param  float|int  $size  The size to format.
     * @param  bool  $isBits  Whether to use bit units (default: false for bytes).
     * @param  array|null  $labels  Optional map of custom labels for units.
     * @param  bool|int  $isBinary  Optional. Whether to use binary (1024) or decimal (1000) base. Default true.
     * @return string Human-readable formatted string (e.g. "1.5 MB").
     */
    public static function readable($size, bool $isBits = false, ?array $labels = [], bool $isBinary = true): string
    {
        $unit = static::detectUnit($size, $isBits, $finalSize, $isBinary);

        if ($labels && isset($labels[$unit])) {
            $unit = $labels[$unit];
        }

        return static::format($finalSize).' '.$unit;
    }

    /**
     * Converts a size with unit to a human-readable string.
     *
     * @param  float|int  $size  The numeric value to convert.
     * @param  string  $unit  Unit of the value (e.g. "MB", "Kb").
     * @param  array|null  $labels  Optional custom unit labels.
     * @param  bool|int  $isBinary  Optional. Whether to use binary (1024) or decimal (1000) base. Default true.
     * @return string Human-readable formatted string.
     */
    public static function readableFromUnit($size, string $unit, ?array $labels = [], bool $isBinary = true): string
    {
        $is_bits = strpos($unit, 'b');

        $bytes = static::convert($size, $unit, $is_bits !== false ? 'b' : 'B', false, $isBinary);

        return static::readable($bytes, $is_bits, $labels, $isBinary);
    }

    /**
     * Parses a human-readable size string like "1.5 GB" or "200 Kb".
     *
     * @param  string  $input  Human-readable size string.
     * @return array [$size, $unit].
     * @throws InvalidArgumentException If input format or unit is invalid.
     */
    public static function parse(string $input): array
    {
        if (!preg_match('/^\s*([\d.]+)\s*([a-zA-Z]+)\s*$/', $input, $matches)) {
            throw new InvalidArgumentException("Invalid size string: $input");
        }

        $size = (float) $matches[1];
        $unit = $matches[2];

        if (!static::isValidUnit($unit)) {
            throw new InvalidArgumentException("Invalid unit in string: $unit");
        }

        return [$size, $unit];
    }

    /**
     * Calculates what percentage size1 is of size2.
     *
     * @param  float|int  $size1  First size value.
     * @param  string  $unit1  Unit of the first size.
     * @param  float|int  $size2  Second size value.
     * @param  string  $unit2  Unit of the second size.
     * @param  bool|int  $isBinary  Optional. Whether to use binary (1024) or decimal (1000) base. Default true.
     * @return float Percentage value.
     */
    public static function percentage($size1, string $unit1, $size2, string $unit2, bool $isBinary = true): float
    {
        $bytes1 = static::convert($size1, $unit1, 'B', false, $isBinary);
        $bytes2 = static::convert($size2, $unit2, 'B', false, $isBinary);

        return ($bytes2 == 0) ? 0.0 : ($bytes1 / $bytes2) * 100;
    }
}