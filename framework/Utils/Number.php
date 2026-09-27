<?php

namespace Footup\Utils;

/**
 * Number utility class for formatting and manipulating numbers
 * 
 * Provides methods for:
 * - Formatting numbers with thousand separators
 * - Abbreviating large numbers (1M, 2.5B, etc.)
 * - Percentage calculations
 * - Rounding operations
 * - Locale-aware formatting
 * 
 * @package Footup
 */
class Number
{
    /**
     * Format a number with thousand separators
     * 
     * @param float|int $number The number to format
     * @param int $decimals Number of decimal places
     * @param string $decimalSep Decimal separator (default: locale-based)
     * @param string $thousandsSep Thousands separator (default: locale-based)
     * @return string Formatted number
     * 
     * @example
     * Number::format(1234.567, 2)  // "1,234.57"
     * Number::format(1234.567, 2, ',', '.')  // "1.234,57"
     */
    public static function format(
        float|int $number,
        int $decimals = 0,
        string $decimalSep = '.',
        string $thousandsSep = ','
    ): string {
        return number_format((float)$number, $decimals, $decimalSep, $thousandsSep);
    }

    /**
     * Abbreviate large numbers
     * 
     * @param float|int $number The number to abbreviate
     * @param int $precision Decimal places in abbreviation
     * @return string Abbreviated number (e.g., "1.2M")
     * 
     * @example
     * Number::abbreviate(1000)        // "1K"
     * Number::abbreviate(1500000)     // "1.5M"
     * Number::abbreviate(2500000000)  // "2.5B"
     */
    public static function abbreviate(float|int $number, int $precision = 1): string
    {
        $number = (float)$number;
        $abs = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($abs >= 1_000_000_000) {
            return $sign . round($abs / 1_000_000_000, $precision) . 'B';
        }

        if ($abs >= 1_000_000) {
            return $sign . round($abs / 1_000_000, $precision) . 'M';
        }

        if ($abs >= 1_000) {
            return $sign . round($abs / 1_000, $precision) . 'K';
        }

        return (string)$number;
    }

    /**
     * Calculate percentage of a number
     * 
     * @param float|int $number The number
     * @param float|int $percent The percentage
     * @return float The calculated percentage
     * 
     * @example
     * Number::percent(200, 15)  // 30 (15% of 200)
     */
    public static function percent(float|int $number, float|int $percent): float
    {
        return ((float)$number / 100) * (float)$percent;
    }

    /**
     * Calculate percentage increase
     * 
     * @param float|int $original Original number
     * @param float|int $increase Increase percentage
     * @return float Result after percentage increase
     * 
     * @example
     * Number::percentIncrease(100, 10)  // 110
     */
    public static function percentIncrease(float|int $original, float|int $increase): float
    {
        return ((float)$original * (1 + (float)$increase / 100));
    }

    /**
     * Calculate percentage decrease
     * 
     * @param float|int $original Original number
     * @param float|int $decrease Decrease percentage
     * @return float Result after percentage decrease
     * 
     * @example
     * Number::percentDecrease(100, 10)  // 90
     */
    public static function percentDecrease(float|int $original, float|int $decrease): float
    {
        return ((float)$original * (1 - (float)$decrease / 100));
    }

    /**
     * Calculate what percentage one number is of another
     * 
     * @param float|int $number The number
     * @param float|int $total The total
     * @param int $decimals Decimal places
     * @return float Percentage value
     * 
     * @example
     * Number::percentOf(30, 200)  // 15 (30 is 15% of 200)
     */
    public static function percentOf(float|int $number, float|int $total, int $decimals = 2): float
    {
        if ((float)$total === 0.0) {
            return 0.0;
        }

        return round(((float)$number / (float)$total) * 100, $decimals);
    }

    /**
     * Round a number
     * 
     * @param float|int $number The number to round
     * @param int $precision Decimal places
     * @return float Rounded number
     * 
     * @example
     * Number::round(1.567, 2)  // 1.57
     */
    public static function round(float|int $number, int $precision = 0): float
    {
        return round((float)$number, $precision);
    }

    /**
     * Ceiling (round up)
     * 
     * @param float|int $number The number
     * @param int $precision Decimal places
     * @return float Ceiling value
     * 
     * @example
     * Number::ceil(1.234, 2)  // 1.24
     */
    public static function ceil(float|int $number, int $precision = 0): float
    {
        $factor = 10 ** $precision;
        return ceil((float)$number * $factor) / $factor;
    }

    /**
     * Floor (round down)
     * 
     * @param float|int $number The number
     * @param int $precision Decimal places
     * @return float Floor value
     * 
     * @example
     * Number::floor(1.987, 2)  // 1.98
     */
    public static function floor(float|int $number, int $precision = 0): float
    {
        $factor = 10 ** $precision;
        return floor((float)$number * $factor) / $factor;
    }

    /**
     * Clamp a number between min and max
     * 
     * @param float|int $number The number to clamp
     * @param float|int $min Minimum value
     * @param float|int $max Maximum value
     * @return float|int Clamped value
     * 
     * @example
     * Number::clamp(15, 0, 10)    // 10
     * Number::clamp(-5, 0, 10)    // 0
     * Number::clamp(5, 0, 10)     // 5
     */
    public static function clamp(float|int $number, float|int $min, float|int $max): float|int
    {
        return max($min, min($max, $number));
    }

    /**
     * Check if number is even
     * 
     * @param int $number The number
     * @return bool True if even
     * 
     * @example
     * Number::isEven(4)   // true
     * Number::isEven(3)   // false
     */
    public static function isEven(int $number): bool
    {
        return $number % 2 === 0;
    }

    /**
     * Check if number is odd
     * 
     * @param int $number The number
     * @return bool True if odd
     * 
     * @example
     * Number::isOdd(5)   // true
     * Number::isOdd(4)   // false
     */
    public static function isOdd(int $number): bool
    {
        return $number % 2 !== 0;
    }

    /**
     * Generate a random number between min and max
     * 
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return int Random number
     * 
     * @example
     * Number::random(1, 100)  // Random between 1-100
     */
    public static function random(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Generate random float between 0 and 1
     * 
     * @return float Random float
     * 
     * @example
     * Number::randomFloat()  // 0.123456...
     */
    public static function randomFloat(): float
    {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    /**
     * Parse a formatted number string to float
     * 
     * @param string $number Formatted number string
     * @param string $decimalSep Decimal separator
     * @param string $thousandsSep Thousands separator
     * @return float Parsed number
     * 
     * @example
     * Number::parse("1,234.56")        // 1234.56
     * Number::parse("1.234,56", ",", ".")  // 1234.56
     */
    public static function parse(
        string $number,
        string $decimalSep = '.',
        string $thousandsSep = ','
    ): float {
        // Remove thousands separator
        $number = str_replace($thousandsSep, '', $number);
        // Replace decimal separator with dot for PHP parsing
        $number = str_replace($decimalSep, '.', $number);
        return (float)$number;
    }

    /**
     * Check if value is numeric
     * 
     * @param mixed $value The value to check
     * @return bool True if numeric
     * 
     * @example
     * Number::isNumeric(123)      // true
     * Number::isNumeric("123")    // true
     * Number::isNumeric("123abc") // false
     */
    public static function isNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Convert number to words (English only)
     * 
     * @param int $number The number (0-999,999,999)
     * @return string Number in words
     * 
     * @example
     * Number::toWords(123)  // "one hundred twenty-three"
     */
    public static function toWords(int $number): string
    {
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        $teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        $scales = ['', 'thousand', 'million', 'billion'];

        if ($number === 0) {
            return 'zero';
        }

        $number = abs($number);
        $groups = [];
        $groupIndex = 0;

        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = intdiv($number, 1000);
            $groupIndex++;
        }

        $parts = [];

        foreach (array_reverse($groups) as $index => $group) {
            if ($group === 0) {
                continue;
            }

            $groupText = '';

            // Hundreds place
            if ($group >= 100) {
                $groupText .= $ones[intdiv($group, 100)] . ' hundred';
                $group %= 100;
                if ($group > 0) {
                    $groupText .= ' ';
                }
            }

            // Tens and ones
            if ($group >= 20) {
                $groupText .= $tens[intdiv($group, 10)];
                if ($group % 10 > 0) {
                    $groupText .= '-' . $ones[$group % 10];
                }
            } elseif ($group >= 10) {
                $groupText .= $teens[$group - 10];
            } elseif ($group > 0) {
                $groupText .= $ones[$group];
            }

            // Add scale word
            $scaleIndex = count($groups) - 1 - $index;
            if ($scaleIndex > 0 && $scaleIndex < count($scales)) {
                $groupText .= ' ' . $scales[$scaleIndex];
            }

            $parts[] = $groupText;
        }

        return implode(' ', $parts);
    }

    /**
     * Format number as ordinal (1st, 2nd, 3rd, etc.)
     * 
     * @param int $number The number
     * @return string Ordinal string
     * 
     * @example
     * Number::ordinal(1)   // "1st"
     * Number::ordinal(2)   // "2nd"
     * Number::ordinal(3)   // "3rd"
     * Number::ordinal(11)  // "11th"
     */
    public static function ordinal(int $number): string
    {
        $suffix = match (true) {
            $number % 100 >= 11 && $number % 100 <= 13 => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number . $suffix;
    }

    /**
     * Calculate average of numbers
     * 
     * @param float|int ...$numbers Variable number of numbers
     * @return float Average value
     * 
     * @example
     * Number::average(10, 20, 30)  // 20
     */
    public static function average(float|int ...$numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }

        return array_sum($numbers) / count($numbers);
    }

    /**
     * Find maximum value
     * 
     * @param float|int ...$numbers Variable number of numbers
     * @return float|int|null Maximum value or null if empty
     * 
     * @example
     * Number::max(10, 50, 30)  // 50
     */
    public static function max(float|int ...$numbers): float|int|null
    {
        return !empty($numbers) ? max($numbers) : null;
    }

    /**
     * Find minimum value
     * 
     * @param float|int ...$numbers Variable number of numbers
     * @return float|int|null Minimum value or null if empty
     * 
     * @example
     * Number::min(10, 50, 30)  // 10
     */
    public static function min(float|int ...$numbers): float|int|null
    {
        return !empty($numbers) ? min($numbers) : null;
    }
}
