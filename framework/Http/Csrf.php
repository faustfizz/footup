<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| CSRF — token generation and validation
|
| Usage:
|   csrf_token()  // get current token for forms
|   // use in middleware: new CsrfMiddleware()
|--------------------------------------------------------------------------
*/

class Csrf
{
    public static function token(): string
    {
        session_start();

        if (! isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verify(string $token): bool
    {
        session_start();

        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}