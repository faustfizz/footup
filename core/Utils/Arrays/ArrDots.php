<?php

namespace Footup\Utils\Arrays;

use ArrayAccess;

/**
 * Class ArrDots
 *
 * @package Footup\Utils\Arrays
 */
class ArrDots
{
    /**
     * Implode a multi-dimensional associative array into a single level dots array.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public static function implode($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::implode($value, $prepend . $key . '.'));
            }
            else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Explode a single level dots array into a multi-dimensional associative array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function explode($array)
    {
        $results = [];

        foreach ($array as $key => $value) {
            static::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * Get the values from a single column in $array.
     * An array of columns can be provided to chain call column.
     *
     * @param array  $array
     * @param string $dots
     * @param string $indexKey Only applied to the last column
     * @return array
     * @see \Footup\Utils\Arrays\Arr::column()
     * @see \array_column()
     */
    public static function column($array, $dots, $indexKey = null)
    {
        return Arr::column($array, explode('.', $dots), $indexKey);
    }

    /**
     * Pluck the values from a single column in `$array`.
     * A `$wildcard` can be set to collapse the array at that point.
     * An array of columns can be provided to chain call column.
     *
     * @param array  $array
     * @param string $dots
     * @param string $wildcard
     * @return array
     * @see \Footup\Utils\Arrays\Arr::column()
     * @see \array_column()
     */
    public static function pluck($array, $dots, $wildcard = null)
    {
        $dots = array_map(function ($i) use ($wildcard) {
            return $i === $wildcard ? null : $i;
        }, explode('.', $dots));
        return Arr::pluck($array, $dots);
    }

    /**
     * @param ArrayAccess|array $array
     * @param array|string      $keys
     *
     * @return void
     */
    public static function remove(&$array, $keys)
    {
        $original = &$array;
        $keys     = (array) $keys;

        if (!Arr::accessible($array)) {
            return;
        }
        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (Arr::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            // Clean up before each pass
            $array = &$original;
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get an item from a multi-dimensional associative array using "dots" notation.
     *
     * @param ArrayAccess|array $array
     * @param string            $key
     * @param mixed             $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!Arr::accessible($array)) {
            return $default;
        }

        if (null === $key) {
            return $array;
        }

        if (Arr::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (Arr::accessible($array) && Arr::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Get all items from a multi-dimensional associative array using "dots" notation and
     * return a flattened "dots" notation array.
     *
     * @param ArrayAccess|array  $array
     * @param string             $key
     * @param null|string        $wildcard
     *
     * @return array|mixed[]
     */
    public static function collate($array, $key, $wildcard = null)
    {
        // If no wildcard set or the wildcard is not in the key
        if (null === $wildcard || strpos($key, $wildcard) === false) {
            return static::has($array, $key) ? [$key => static::get($array, $key)] : [];
        }

        $pattern  = '';
        $segments = explode('.', $key);
        while (($segment = array_shift($segments)) !== null) {
            // If we have run out of arrays to look into, stop looking
            if (!Arr::accessible($array)) {
                return [];
            }

            // If this segment is a wildcard
            if ($segment === $wildcard) {
                $values = [];
                foreach (array_keys($array) as $attr) {
                    $subKey = implode('.', array_merge([$attr], $segments));
                    foreach (static::collate($array, $subKey, $wildcard) as $attrKey => $value) {
                        $values[$pattern . $attrKey] = $value;
                    }
                }

                return $values;
            }
            if (Arr::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return [];
            }

            $pattern .= $segment . '.';
        }

        return [$key => $array];
    }

    /**
     * Determine if an item or items exist in an multi-dimensional associative array using "dots" notation.
     *
     * @param ArrayAccess|array $array
     * @param string|string[]   $keys
     * @param null|string       $wildcard
     *
     * @return bool
     */
    public static function has($array, $keys, $wildcard = null)
    {
        // If the keys are null or the array is not accessible
        if (null === $keys || empty($array) || !Arr::accessible($array)) {
            return false;
        }

        // Check that every key exists in $array
        $originalArray = $array;
        foreach ((array) $keys as $key) {
            $array = $originalArray;

            // If the array has the key carry on
            if (Arr::exists($array, $key)) {
                continue;
            }

            // Break up the key into segments and drill into the array
            $segments = explode('.', $key);
            foreach ($segments as $k => $segment) {
                // If the segment is a wildcard
                if ($segment === $wildcard && !empty($array)) {
                    // If this is the last segment then the array has the key
                    if ($k + 1 === count($segments)) {
                        break;
                    }
                    // If we are still considering an array, drill into every possibility
                    if (Arr::accessible($array)) {
                        // Check that at least one possibility contains the (sub)key
                        $subKey = implode('.', array_slice($segments, $k + 1));
                        $found  = array_reduce($array, function ($f, $item) use ($subKey, $wildcard) {
                            return $f || static::has($item, $subKey, $wildcard);
                        }, false);
                        if (!$found) {
                            return false;
                        } else {
                            break;
                        }
                    }
                }
                // Otherwise continue to drill into $array
                if (!empty($array) && Arr::accessible($array) && Arr::exists($array, $segment)) {
                    $array = $array[$segment];
                    continue;
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Get a value from the array and remove it.
     *
     * @param ArrayAccess|array $array
     * @param string            $key
     * @param mixed             $default
     *
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::remove($array, $key);

        return $value;
    }

    /**
     * Set an multi-dimensional associative array item to $value using "dots" notation.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (null === $key) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Get a subset of items from an multi-dimensional associative $array using "dots" notation for $keys.
     *
     * @param array           $array
     * @param string|string[] $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        $imploded = static::implode($array);
        $only     = Arr::only($imploded, $keys);
        return static::explode($only);
    }

    /**
     * Given a multi-dimensional array, return the first item that has a property $property
     * with value $value.
     * An array of properties can be provided to perform deeper finds.
     *
     * @param array  $array
     * @param string $dots
     * @param mixed  $value
     * @param bool   $strict
     * @return mixed|null
     *
     * @see \Footup\Utils\Arrays\Arr::locate()
     */
    public static function locate($array, $dots, $value, $strict = false)
    {
        return Arr::locate($array, explode('.', $dots), $value, $strict);
    }
}