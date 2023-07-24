<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder;

class Helper
{
    /**
     * Check if string contains any of the needles.
     *
     * @param string $haystack The string to check.
     * @param string[] $needles The needles to check for.
     * @return bool Whether the haystack contains any of the needles.
     */
    public static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Escape the given value to comply with the PostgREST API.
     *
     * @param string|int|float $value The value to escape.
     * @return string The escaped value.
     */
    public static function escapeString(string|int|float $value): string
    {
        if (is_int($value) || is_float($value)) {
            $value = strval($value);
        } elseif (self::containsAny($value, [',', '(', ')', '.', ':', '"', '\\'])) {
            $value = '"' . str_replace('"', '\"', str_replace('\\', '\\\\', $value)) . '"';
        }

        return $value;
    }

    /**
     * Escape each element in the given array and implode them with the given open and close strings.
     *
     * @param string[]|int[]|float[] $value The array to implode.
     * @param string $open The open string.
     * @param string $close The close string.
     * @return string The imploded string.
     */
    public static function implodeWithBraces(array $value, string $open, string $close): string
    {
        $escaped = [];
        foreach ($value as $word) {
            array_push($escaped, self::escapeString(strval($word)));
        }

        return sprintf('%s%s%s', $open, implode(',', $escaped), $close);
    }

    /**
     * Check if all values in the given array are of the same type.
     *
     * @param array<int|string, string|int|float> $array The array to check.
     * @return bool Returns true if all values are of the same type.
     */
    public static function checkUnifiedValueTypes(array $array): bool
    {
        return array_reduce($array, function ($carry, $item) {
            if ($carry === null) {
                return gettype($item);
            }
            if ($carry !== gettype($item)) {
                return false;
            }
            return $carry;
        }, null) !== false;
    }

    /**
     * Convert the given data to CSV.
     *
     * @param array<array<string, mixed>> $data The data to convert.
     * @return string The CSV.
     */
    public static function convertToCSV(array $data): string
    {
        $header = [];
        foreach ($data as $row) {
            foreach (array_keys($row) as $key) {
                if (! in_array($key, $header, true)) {
                    $header[] = $key;
                }
            }
        }

        $csv = implode(',', $header) . "\n";

        foreach ($data as $row) {
            $line = [];
            foreach ($header as $key) {
                if (array_key_exists($key, $row) && $row[$key] === null) {
                    $line[] = 'NULL';
                    continue;
                }
                if (array_key_exists($key, $row)) {
                    $line[] = $row[$key];
                    continue;
                }

                $line[] = '';
            }
            $csv .= implode(',', $line) . "\n";
        }

        return $csv;
    }
}
