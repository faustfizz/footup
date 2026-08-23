<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| i18n — simple translation system
|
| Usage:
|   __('messages.welcome') // looks in resources/lang/{locale}/messages.php
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
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $key = implode('.', $parts);

        $messages = self::load($file, $locale);
        $value = $messages;

        foreach ($parts as $part) {
            $value = $value[$part] ?? $key;
        }

        foreach ($replace as $search => $replace_val) {
            $value = str_replace(':' . $search, $replace_val, $value);
        }

        return $value;
    }

    protected static function load(string $file, $locale = null): array
    {
        $key = ($locale ?? self::$locale) . '.' . $file;

        if (isset(self::$messages[$key])) {
            return self::$messages[$key];
        }

        $path = base_path(app()->uiPath . "/lang/" . ($locale ?? self::$locale) . "/{$file}.");

        if (is_file($path . 'json')) {
          self::$messages[$key] = json_decode(
            file_get_contents($path)
            , true
          );
        } elseif (is_file($path . 'php')) {
          self::$messages[$key] = require $path;
        } else {
          return [];
        }

        return self::$messages[$key];
    }
}
