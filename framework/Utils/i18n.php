<?php

declare(strict_types=1);

namespace Footup\Utils;

/*
|--------------------------------------------------------------------------
| i18n — simple translation system
|
| Usage:
|   __('messages.welcome') // looks in /lang/{locale}/messages.php
|   trans('auth.failed')   // same thing
|   locale('fr')           // set current locale
|--------------------------------------------------------------------------
*/

class i18n
{
  protected static string $locale = 'en';
  protected static array $messages = [];

  public static function setLocale(string $locale): void
  {
    self::$locale = $locale;
    self::$messages = [];
  }

  public static function locale(): string
  {
    return self::$locale;
  }

  public static function trans(string $key, array $replace = [], $locale = null): string
  {
    self::$locale = $locale ?? self::$locale;
    // Check if this is a proper dotted key (word.word pattern)
    // Dotted keys contain only letters, numbers, underscores, and dots
    // and don't have spaces or special characters (except dots)
    $isDotted = preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/', $key);

    if ($isDotted) {
      // TIER 1: File-based translation (dotted notation)
      $parts = explode('.', $key);
      $file = array_shift($parts);
      $remaining_key = implode('.', $parts);

      $messages = self::load($file);
      $value = $messages;

      foreach ($parts as $part) {
        if (is_array($value) && isset($value[$part])) {
          $value = $value[$part];
        } else {
          $value = $key;  // Fallback to the full key
          break;
        }
      }
    } else {
      // TIER 2: Flat JSON (simple strings with spaces, punctuation, etc)
      // Try to find in locale-specific flat JSON file first
      $messages = self::loadFlatJson();

      if ($messages && isset($messages[$key])) {
        // Found in flat JSON file
        $value = $messages[$key];
      } else {
        // TIER 3: Not found in file, use string as fallback
        $value = $key;
      }
    }

    // Convert to string if needed
    if (!is_string($value)) {
      $value = $key;
    }

    // Auto-detect choice syntax when 'count' parameter exists
    if (strpos($value, '|') !== false && isset($replace['count'])) {
      return self::choice($value, $replace['count'], $replace);
    }

    // Support both :key and {key} syntax for placeholders
    foreach ($replace as $search => $replace_val) {
      $value = str_replace(':' . $search, $replace_val, $value);
      $value = str_replace('{' . $search . '}', $replace_val, $value);
    }

    return $value;
  }

  /**
   * Handle pluralization in translated messages
   * 
   * Syntax:
   *   {0} message for zero
   *   {1} message for one
   *   [2,5] message for 2-5
   *   [6,*] message for 6 and above
   *   * fallback message
   * 
   * Example:
   *   'items' => '{0} No items|{1} One item|[2,*] :count items'
   */
  public static function choice(string $message, int $count, array $replace = []): string
  {
    $segments = explode('|', $message);

    $selectedSegment = self::getSegmentForCount($count, $segments);

    if ($selectedSegment === null) {
      return $message;
    }

    // Replace placeholders
    $result = trim($selectedSegment);

    foreach ($replace as $search => $replace_val) {
      $result = str_replace(':' . $search, $replace_val, $result);
      $result = str_replace('{' . $search . '}', $replace_val, $result);
    }

    return $result;
  }

  /**
   * Find the appropriate segment based on count
   */
  private static function getSegmentForCount(int $count, array $segments): ?string
  {
    foreach ($segments as $segment) {
      $segment = trim($segment);

      // Check exact match: {0}, {1}, {2}, etc
      if (preg_match('/^\{(\d+)\}\s+/', $segment, $matches)) {
        if ((int)$matches[1] === $count) {
          return preg_replace('/^\{\d+\}\s+/', '', $segment);
        }
        continue;
      }

      // Check range match with CURLY BRACES (new unified syntax): {2,5}, {6,*}, etc
      if (preg_match('/^\{(\d+),(\d+|\*)\}\s+/', $segment, $matches)) {
        $from = (int)$matches[1];
        $to = $matches[2] === '*' ? PHP_INT_MAX : (int)$matches[2];

        if ($count >= $from && $count <= $to) {
          return preg_replace('/^\{\d+,(\d+|\*)\}\s+/', '', $segment);
        }
        continue;
      }

      // Check range match with SQUARE BRACKETS (old syntax, deprecated but still supported): [0,5], [6,*], etc
      if (preg_match('/^\[(\d+),(\d+|\*)\]\s+/', $segment, $matches)) {
        $from = (int)$matches[1];
        $to = $matches[2] === '*' ? PHP_INT_MAX : (int)$matches[2];

        if ($count >= $from && $count <= $to) {
          return preg_replace('/^\[\d+,(\d+|\*)\]\s+/', '', $segment);
        }
        continue;
      }

      // Check fallback: *
      if (trim($segment, ' *') === '') {
        return preg_replace('/^\*\s+/', '', $segment);
      }
    }

    // Return first segment as fallback
    return !empty($segments) ? trim($segments[0]) : null;
  }

  /**
   * Load flat JSON file for inline translations
   * e.g., /lang/{locale}.json
   * Contains simple keys without nesting
   */
  protected static function loadFlatJson(): ?array
  {
    $cacheKey = 'flat.' . self::$locale;

    if (isset(self::$messages[$cacheKey])) {
      return self::$messages[$cacheKey];
    }

    // Try to load {locale}.json (e.g., en.json, fr.json)
    $path = ui_path("/lang/" . self::$locale . ".json");

    if (!is_file($path)) {
      return null;
    }

    $json = file_get_contents($path);
    $messages = json_decode($json, true);

    if (!is_array($messages)) {
      return null;
    }

    self::$messages[$cacheKey] = $messages;
    return $messages;
  }

  /**
   * Load translation file
   * Tries: {locale}/{file}.php, {locale}/{file}.json, {file}.{locale}.json
   */
  protected static function load(string $file): array
  {
    $key = self::$locale . '.' . $file;

    if (isset(self::$messages[$key])) {
      return self::$messages[$key];
    }

    $messages = [];

    // Try PHP file first
    $phpPath = ui_path("/lang/" . self::$locale . "/{$file}.php");
    if (is_file($phpPath)) {
      $messages = require $phpPath;
      self::$messages[$key] = $messages;
      return $messages;
    }

    // Try JSON file
    $jsonPath = ui_path("/lang/" . self::$locale . "/{$file}.json");
    if (is_file($jsonPath)) {
      $json = file_get_contents($jsonPath);
      $messages = json_decode($json, true) ?? [];
      self::$messages[$key] = $messages;
      return $messages;
    }

    // Also try flat JSON format: messages.en.json, messages.fr.json
    $flatJsonPath = ui_path("/lang/{$file}." . self::$locale . ".json");
    if (is_file($flatJsonPath)) {
      $json = file_get_contents($flatJsonPath);
      $messages = json_decode($json, true) ?? [];
      self::$messages[$key] = $messages;
      return $messages;
    }

    return [];
  }
}
