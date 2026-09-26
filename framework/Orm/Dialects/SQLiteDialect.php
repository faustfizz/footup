<?php

declare(strict_types=1);

namespace Footup\Orm\Dialects;

class SQLiteDialect extends Dialect
{
    public function autoIncrement(string $columnType = 'integer'): string
    {
        return 'AUTOINCREMENT';
    }

    public function getLastInsertIdSql(string $table, string $column = 'id'): string
    {
        return 'last_insert_rowid()';
    }

    public function typeMap(string $footupType): string
    {
        return match ($footupType) {
            'string' => 'TEXT',
            'text' => 'TEXT',
            'integer' => 'INTEGER',
            'bigInteger' => 'INTEGER',
            'boolean' => 'INTEGER',
            'timestamp' => 'DATETIME',
            'datetime' => 'DATETIME',
            'decimal' => 'REAL',
            'json' => 'TEXT',
            default => $footupType,
        };
    }

    public function supports(string $feature): bool
    {
        return match ($feature) {
            'transactions', 'foreign_keys', 'indexes' => true,
            default => false,
        };
    }

    public function escapeIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
