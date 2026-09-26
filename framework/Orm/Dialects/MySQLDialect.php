<?php

declare(strict_types=1);

namespace Footup\Orm\Dialects;

class MySQLDialect extends Dialect
{
    public function autoIncrement(string $columnType = 'integer'): string
    {
        return 'AUTO_INCREMENT';
    }

    public function getLastInsertIdSql(string $table, string $column = 'id'): string
    {
        return 'LAST_INSERT_ID()';
    }

    public function typeMap(string $footupType): string
    {
        return match ($footupType) {
            'string' => 'VARCHAR(255)',
            'text' => 'TEXT',
            'integer' => 'INT',
            'bigInteger' => 'BIGINT',
            'boolean' => 'BOOLEAN',
            'timestamp' => 'TIMESTAMP',
            'datetime' => 'DATETIME',
            'decimal' => 'DECIMAL(8,2)',
            'json' => 'JSON',
            default => $footupType,
        };
    }

    public function supports(string $feature): bool
    {
        return match ($feature) {
            'transactions', 'foreign_keys', 'indexes', 'json', 'fulltext' => true,
            default => false,
        };
    }

    public function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
