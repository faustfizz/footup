<?php

declare(strict_types=1);

namespace Footup\Database;

/*
|--------------------------------------------------------------------------
| Thin PDO wrapper — one connection, built from
| config/database.php. Every statement()/select() call reports its
| timing into Core\Benchmark automatically.
|--------------------------------------------------------------------------
*/

class Connection
{
    protected \PDO $pdo;
    protected string $driver;

    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'sqlite';

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

    public function insertGetId(string $sql, array $bindings = []): string
    {
        $this->statement($sql, $bindings);

        return $this->pdo->lastInsertId();
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
