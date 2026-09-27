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
  public static function __callStatic(string $name, $arguments)
  {
    $strClass = new self();
    if (method_exists($strClass, $name)) {
      return $strClass->{$name}(...$arguments);
    }
    return null;
  }

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
    $overlap = explode('.', $overlap);
    $attribute = explode('.', $attribute);
    $field = explode('.', $field);

    for ($i = 0; $i < count($overlap); $i++) {
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

  /**
   * Escape HTML special characters (safe in templates)
   * Use this for ANY user input in HTML: $user->bio, comments, etc.
   */
  public static function escape(?string $string): string
  {
    if ($string === null) {
      return '';
    }

    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Alias for escape() — shorter name
   */
  public static function e(?string $string): string
  {
    return self::escape($string);
  }

  /**
   * Convert string to URL-safe slug
   * e.g. "Hello World!" → "hello-world"
   */
  public static function slug(string $string): string
  {
    $string = self::escape($string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');

    return $string;
  }

  /**
   * Limit string to N characters with suffix
   * e.g. "Hello World!", 8 → "Hello..."
   */
  public static function limit(string $string, int $limit = 100, string $end = '...'): string
  {
    if (strlen($string) <= $limit) {
      return $string;
    }

    return substr($string, 0, $limit) . $end;
  }

  /**
   * Check if string contains substring
   */
  public static function contains(string $haystack, string $needle): bool
  {
    return str_contains($haystack, $needle);
  }

  /**
   * Check if string starts with substring
   */
  public static function startsWith(string $haystack, string $needle): bool
  {
    return str_starts_with($haystack, $needle);
  }

  /**
   * Check if string ends with substring
   */
  public static function endsWith(string $haystack, string $needle): bool
  {
    return str_ends_with($haystack, $needle);
  }

  /**
   * Capitalize first letter
   */
  public static function capitalize(string $string): string
  {
    return ucfirst($string);
  }

  /**
   * UPPERCASE all
   */
  public static function upper(string $string): string
  {
    return strtoupper($string);
  }

  /**
   * lowercase all
   */
  public static function lower(string $string): string
  {
    return strtolower($string);
  }

  /**
   * Convert camelCase to snake_case
   * e.g. "firstName" → "first_name"
   */
  public static function snake(string $string): string
  {
    $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);

    return strtolower($string);
  }

  /**
   * Convert snake_case to camelCase
   * e.g. "first_name" → "firstName"
   */
  public static function camel(string $string): string
  {
    $parts = explode('_', $string);
    $camel = array_shift($parts);

    foreach ($parts as $part) {
      $camel .= ucfirst($part);
    }

    return $camel;
  }

  /**
   * Pluralize English noun
   * e.g. "post" → "posts", "box" → "boxes", "category" → "categories"
   */
  public static function plural(string $word): string
  {
    // Irregular plurals
    $irregulars = [
      'child' => 'children',
      'man' => 'men',
      'woman' => 'women',
      'person' => 'people',
      'mouse' => 'mice',
      'tooth' => 'teeth',
      'foot' => 'feet',
      'goose' => 'geese',
      'ox' => 'oxen',
      'deer' => 'deer',
      'sheep' => 'sheep',
      'fish' => 'fish',
    ];

    $lower = strtolower($word);

    if (isset($irregulars[$lower])) {
      return $word[0] === strtoupper($word[0])
        ? ucfirst($irregulars[$lower])
        : $irregulars[$lower];
    }

    // Standard pluralization rules
    // Words ending in s, x, z, ch, sh → add es
    if (preg_match('/([sxz]|ch|sh)$/', $word)) {
      return $word . 'es';
    }

    // Words ending in consonant + y → change y to ies
    if (preg_match('/[^aeiou]y$/', $word)) {
      return substr($word, 0, -1) . 'ies';
    }

    // Words ending in f or fe → change to ves
    if (preg_match('/fe?$/', $word)) {
      return preg_replace('/fe?$/', 'ves', $word);
    }

    // Words ending in o (sometimes add es, usually just s for modern usage)
    if (preg_match('/[^aeiou]o$/', $word)) {
      return $word . 'es';
    }

    // Default: add s
    return $word . 's';
  }
}
