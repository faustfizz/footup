<?php

namespace Footup\Facades;

use Footup\Messaging\Mailer;

/**
 * Mail Facade
 * 
 * Usage:
 *   Mail::to('user@example.com')->subject('Hi')->body('Test')->send();
 */
class Mail
{
    private static ?Mailer $mailer = null;

    /**
     * Get or create Mailer instance
     */
    private static function mailer(): Mailer
    {
        return self::$mailer ??= new Mailer();
    }

    /**
     * Set recipient
     */
    public static function to(string $email): Mailer
    {
        return self::mailer()->to($email);
    }

    /**
     * Get configuration
     */
    public static function config(): array
    {
        return Mailer::config();
    }

    /**
     * Forward method calls to Mailer
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return self::mailer()->$method(...$arguments);
    }
}
