<?php

declare(strict_types=1);

namespace Footup\Database;

use Footup\Application;

/*
|--------------------------------------------------------------------------
| Static convenience wrapper around the bound Connection —
| DB::table(), DB::select(), DB::statement(), DB::transaction(). Purely
| sugar over app(Connection::class); nothing else in the framework
| requires it.
|--------------------------------------------------------------------------
*/

class DB
{
    public static function connection(): Connection
    {
        return Application::current()->make(Connection::class);
    }

    public static function table(string $table): QueryBuilder
    {
        return self::connection()->table($table);
    }

    /** @return array<int, array<string, mixed>> */
    public static function select(string $sql, array $bindings = []): array
    {
        return self::connection()->select($sql, $bindings);
    }

    public static function statement(string $sql, array $bindings = []): bool
    {
        return self::connection()->statement($sql, $bindings);
    }

    public static function transaction(\Closure $callback): mixed
    {
        return self::connection()->transaction($callback);
    }
}
