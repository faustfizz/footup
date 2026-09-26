<?php

declare(strict_types=1);

namespace Footup\Orm\Dialects;

/**
 * Database Dialect - Handles driver-specific SQL generation
 */
abstract class Dialect
{
    abstract public function autoIncrement(string $columnType = 'integer'): string;
    abstract public function getLastInsertIdSql(string $table, string $column = 'id'): string;
    abstract public function typeMap(string $footupType): string;
    abstract public function supports(string $feature): bool;
    abstract public function escapeIdentifier(string $identifier): string;

    public function limit(int $limit, ?int $offset = null): string
    {
        $sql = "LIMIT {$limit}";
        if ($offset !== null) {
            $sql .= " OFFSET {$offset}";
        }
        return $sql;
    }

    public static function for(string $driver): self
    {
        return match ($driver) {
            'mysql' => new MySQLDialect(),
            'pgsql' => new PostgreSQLDialect(),
            'sqlite' => new SQLiteDialect(),
            default => throw new \RuntimeException("Unsupported database driver: {$driver}"),
        };
    }
}
