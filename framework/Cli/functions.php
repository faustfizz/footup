<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Footup\Cli;

/**
 * Translates a message.
 */
function t(string $text, array $args = []): string
{
    $translations = Konsole::$locales[Konsole::$locale] ?? [];
    $text         = $translations[$text] ?? $text;

    return sprintf($text, ...$args);
}
