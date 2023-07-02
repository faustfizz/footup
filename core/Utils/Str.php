<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils;

class Str
{
    /**
     * Prettify the attribute name.
     *
     * @param string $atr
     *
     * @return string
     */
    public static function prettyAttribute($atr)
    {
        return ucfirst(str_replace(['.*', '.', '_'], ['', ' ', ' '], $atr));
    }

    /**
     * Does `$a` overlap with `$b` from the left-hand side.
     *
     * @param string $a
     * @param string $b
     *
     * @return bool|string
     */
    public static function overlapLeft(string $a, string $b)
    {
        if (empty($b)) {
            return false;
        }

        if ($a === $b) {
            return $b;
        }

        if (substr_count($a, '.') > substr_count($b, '.')) {
            return static::overlapLeft(substr($a, 0, strrpos($a, '.')), $b);
        } else {
            return static::overlapLeft($a, substr($b, 0, strrpos($b, '.')));
        }
    }

    /**
     * Merge the overlap of pattern, field, and attribute.
     *
     * @param string $overlap    Str::overlapLeft of a pattern (foo.*.bar) and field (foo.*.bax)
     * @param string $attribute  Realised attribute name (foo.0.bar)
     * @param string $field      Field name (foo.*.bax)
     *
     * @return bool|string
     */
    public static function overlapLeftMerge($overlap, $attribute, $field)
    {
        $overlap   = explode('.', $overlap);
        $attribute = explode('.', $attribute);
        $field     = explode('.', $field);

        for ($i=0; $i<count($overlap); $i++) {
            $field[$i] = $attribute[$i];
        }
        return implode('.', $field);
    }

    /**
     * Get random string
     *
     * @param integer $length
     * @param string $with
     * @return string
     */
    public static function random($length = 16, $with = 'alphanum')
    {
        $characters = $with === 'alphanum' ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_' : (in_array($with, ['num', 'number']) ? '0123456789' : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_');

        $charactersLength = strlen($characters);
        $randomString = '';
        while (strlen($randomString) < $length) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}