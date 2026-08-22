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

    public static function trans(string $key, array $replace = []): string
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $key = implode('.', $parts);

        $messages = self::load($file);
        $value = $messages;

        foreach ($parts as $part) {
            $value = $value[$part] ?? $key;
        }

        foreach ($replace as $search => $replace_val) {
            $value = str_replace(':' . $search, $replace_val, $value);
        }

        return $value;
    }

    protected static function load(string $file): array
    {
        $key = self::$locale . '.' . $file;

        if (isset(self::$messages[$key])) {
            return self::$messages[$key];
        }

        $path = base_path("resources/lang/" . self::$locale . "/{$file}.php");

        if (! is_file($path)) {
            return [];
        }

        self::$messages[$key] = require $path;

        return self::$messages[$key];
    }
}
