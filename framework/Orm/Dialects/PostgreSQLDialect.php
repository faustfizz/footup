<?php

declare(strict_types=1);

namespace Footup\Orm\Dialects;

class PostgreSQLDialect extends Dialect
{
    public function autoIncrement(string $columnType = 'integer'): string
    {
        return $columnType === 'bigInteger' ? 'BIGSERIAL' : 'SERIAL';
    }

    public function getLastInsertIdSql(string $table, string $column = 'id'): string
    {
        $sequence = strtolower("{$table}_{$column}_seq");
        return "currval('{$sequence}')";
    }

    public function typeMap(string $footupType): string
    {
        return match ($footupType) {
            'string' => 'VARCHAR(255)',
            'text' => 'TEXT',
            'integer' => 'INTEGER',
            'bigInteger' => 'BIGINT',
            'boolean' => 'BOOLEAN',
            'timestamp' => 'TIMESTAMP WITH TIME ZONE',
            'datetime' => 'TIMESTAMP',
            'decimal' => 'NUMERIC(8,2)',
            'json' => 'JSONB',
            default => $footupType,
        };
    }

    public function supports(string $feature): bool
    {
        return match ($feature) {
            'transactions', 'foreign_keys', 'indexes', 'json', 'arrays' => true,
            default => false,
        };
    }

    public function escapeIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
