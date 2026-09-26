<?php

declare(strict_types=1);

namespace Footup\Orm;

use Footup\Orm\Dialects\Dialect;

/*
|--------------------------------------------------------------------------
| Thin PDO wrapper — one connection, built from
| config/database.php. Supports MySQL, PostgreSQL, and SQLite.
| Every statement()/select() call reports its timing into Core\Benchmark.
|--------------------------------------------------------------------------
*/

class Connection
{
    protected \PDO $pdo;
    protected string $driver;
    protected Dialect $dialect;

    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'sqlite';
        $this->dialect = Dialect::for($this->driver);

        $dsn = match ($this->driver) {
            'sqlite' => 'sqlite:' . $config['database'],
            'mysql' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 3306,
                $config['database'] ?? '',
                $config['charset'] ?? 'utf8mb4'
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 5432,
                $config['database'] ?? ''
            ),
            default => throw new \RuntimeException("Unsupported database driver [{$this->driver}]."),
        };

        if ($this->driver === 'sqlite' && ! is_dir(dirname($config['database']))) {
            @mkdir(dirname($config['database']), 0775, true);
        }

        $this->pdo = new \PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    public function driver(): string
    {
        return $this->driver;
    }

    public function dialect(): Dialect
    {
        return $this->dialect;
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    /** @return array<int, array<string, mixed>> */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        return $this->select($sql, $bindings)[0] ?? null;
    }

    public function statement(string $sql, array $bindings = []): bool
    {
        return $this->pdo->prepare($sql)->execute($bindings);
    }

    public function insertGetId(string $sql, array $bindings = [], ?string $idColumn = 'id'): string|false
    {
        $this->statement($sql, $bindings);

        // For PostgreSQL with RETURNING clause already in SQL, use it
        if ($this->driver === 'pgsql' && stripos($sql, 'RETURNING') !== false) {
            // Already handled by RETURNING clause
            return $this->selectOne($sql, $bindings)[$idColumn] ?? false;
        }

        // For MySQL and SQLite, use lastInsertId
        if ($this->driver === 'mysql' || $this->driver === 'sqlite') {
            return $this->pdo->lastInsertId();
        }

        // For PostgreSQL without RETURNING, use currval with sequence name
        if ($this->driver === 'pgsql') {
            // Extract table name from SQL (simple pattern matching)
            preg_match('/INSERT\s+INTO\s+["`]?(\w+)["`]?/i', $sql, $matches);
            $table = $matches[1] ?? 'unknown';
            $sequence = strtolower("{$table}_{$idColumn}_seq");
            return $this->selectOne("SELECT currval(?) as id", [$sequence])['id'] ?? '0';
        }

        return false;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function transaction(\Closure $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }
}
