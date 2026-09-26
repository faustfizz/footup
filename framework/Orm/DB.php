<?php

declare(strict_types=1);

namespace Footup\Orm;

use Footup\Engine\Application;

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

    public static function table(string $table, $model = null): QueryBuilder
    {
        return self::connection()->table($table);
    }

    /** @return array<int, array<string, mixed>> */
    public static function select(string $sql, array $bindings = []): array
    {
        return self::connection()->select($sql, $bindings);
    }

    public static function transaction(callable $callback): mixed
    {
        $connection = self::connection();
        $pdo = $connection->pdo();

        try {
            $pdo->beginTransaction();
            $result = call_user_func($callback, $connection);
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }
}
