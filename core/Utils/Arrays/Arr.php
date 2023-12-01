<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils\Arrays
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Utils\Arrays;

use ArrayAccess;

class Arr
{
    /**
     * Determines if $value is an accessible like an array.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determines if $array is associative.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param Arrayable|array $array
     *
     * @return array [array, array]
     */
    public static function divide($array)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param Arrayable|array $array
     * @param int             $depth
     *
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_reduce($array, function ($result, $item) use ($depth) {
            if (!is_array($item)) {
                return array_merge($result, [$item]);
            } else if ($depth === 1) {
                return array_merge($result, array_values($item));
            } else {
                return array_merge($result, static::flatten($item, $depth - 1));
            }
        }, []);
    }

    /**
     * Merge all elements of $array into a single array.
     *
     * @param ArrayAble|array $array
     * @return array
     */
    public static function collapse($array)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_merge(...$array);
    }

    /**
     * Get a subset of the items from $array that only contains $keys.
     *
     * @param Arrayable|array  $array
     * @param int|string|array $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Get a subset of the items from $array that contains all keys except $keys.
     *
     * @param ArrayAble|array  $array
     * @param int|string|array $keys
     *
     * @return array
     */
    public static function except($array, $keys)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_diff_key($array, array_flip((array) $keys));
    }

    /**
     * Get a subset of items from $array that pass $callback test.
     *
     * @param ArrayAble|array $array
     * @param callable        $callback
     *
     * @return array
     */
    public static function filter($array, callable $callback)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get a subset of unique items from $array.
     *
     * @param ArrayAble|array  $array
     * @param int              $flag
     *
     * @return array
     */
    public static function unique($array, $flag = SORT_STRING)
    {
        if ($array instanceof ArrayAble) {
            $array = $array->toArray();
        }
        return array_unique($array, $flag);
    }

    /**
     * Get the values from a single column in $array.
     * An array of columns can be provided to chain call column.
     *
     * @param array        $array
     * @param string|array $columns
     * @param string       $indexKey Only applied to the last column
     * @return array
     * @see \array_column()
     */
    public static function column(array $array = null, $columns, $indexKey = null)
    {
        $array = (array) $array;
        $columns = (array) $columns;
        $last = array_pop($columns);
        foreach ($columns as $column) {
            $array = array_column($array, $column);
        }
        return array_column($array, $last, $indexKey);
    }

    /**
     * Pluck the values from a single column in `$array`.
     * If an element in `$columns` is `null` then collapse the `$array`
     * An array of columns can be provided to chain call column.
     *
     * @param array        $array
     * @param string|array $columns
     * @return array
     * @see \array_column()
     */
    public static function pluck(array $array = null, $columns)
    {
        $array = (array) $array;
        $columns = (array) $columns;
        foreach ($columns as $column) {
            if ($column !== null) {
                $array = array_column($array, $column);
            } else {
                $array = array_merge(...$array);
            }
        }
        return $array;
    }

    /**
     * @param ArrayAccess|array $array
     * @param string|int        $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Search the $haystack and return the first corresponding key if successful.
     * If $needle is a callable then return the first key where the callable
     * returns true.
     *
     * @param array           $haystack
     * @param mixed|callable  $needle
     * @param bool            $strict
     * @return false|int|string
     */
    public static function searchKey(array $haystack, $needle, $strict = false)
    {
        if (is_callable($needle)) {
            foreach ($haystack as $key => $item) {
                $result = $needle($item, $key);
                if ((!$strict && $result == true) || ($strict && $result === true)) {
                    return $key;
                }
            }
            return false;
        } else {
            return array_search($needle, $haystack, $strict);
        }
    }

    /**
     * Search the $haystack and return the first corresponding element if successful.
     * If $needle is a callable then return the first element where the callable
     * returns true.
     *
     * @param array           $haystack
     * @param mixed|callable  $needle
     * @param bool            $strict
     * @return false|int|string
     */
    public static function search(array $haystack, $needle, $strict = false)
    {
        if (is_callable($needle)) {
            foreach ($haystack as $key => $item) {
                $result = $needle($item, $key);
                if ((!$strict && $result == true) || ($strict && $result === true)) {
                    return $item;
                }
            }
            return null;
        } else {
            $key = array_search($needle, $haystack, $strict);
            return $key !== false ? $haystack[$key] : null;
        }
    }

    /**
     * Given a multi-dimensional array, return the first item that has a property $property
     * with value $value.
     * An array of properties can be provided to perform deeper finds.
     *
     * @param array        $array
     * @param string|array $property
     * @param mixed        $value
     * @param bool         $strict
     * @return mixed|null
     */
    public static function locate($array, $property, $value, $strict = false)
    {
        $array = $array ?? [];
        $columns = (array) $property;
        $property = array_pop($columns);
        if (!empty($columns)) {
            $array = static::column($array, $columns);
        }
        foreach ($array as $item) {
            if (!isset($item[$property])) {
                continue;
            }
            if ((!$strict && $item[$property] == $value) || ($strict && $item[$property] === $value)) {
                return $item;
            }
        }
        return null;
    }
}