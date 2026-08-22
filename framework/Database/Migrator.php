<?php

declare(strict_types=1);

namespace Footup\Database;

/*
|--------------------------------------------------------------------------
| Runs plain .sql files from a migrations directory, in
| filename order, once each — tracked in a `migrations` table. No
| schema builder, no up()/down(), just SQL: keeps migrations portable
| and readable at the cost of the DSL a bigger framework would give you.
|--------------------------------------------------------------------------
*/

class Migrator
{
    public function __construct(protected Connection $connection, protected string $path)
    {
    }

    /** @return string[] names of migrations that were run */
    public function run(): array
    {
        $this->ensureMigrationsTable();

        $applied = array_column($this->connection->select('SELECT migration FROM migrations'), 'migration');
        $ran = [];

        $files = glob(rtrim($this->path, '/') . '/*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.sql');

            if (in_array($name, $applied, true)) {
                continue;
            }

            foreach (array_filter(array_map('trim', explode(';', (string) file_get_contents($file)))) as $statement) {
                $this->connection->statement($statement);
            }

            $this->connection->statement(
                'INSERT INTO migrations (migration, ran_at) VALUES (?, ?)',
                [$name, date('Y-m-d H:i:s')]
            );

            $ran[] = $name;
        }

        return $ran;
    }

    protected function ensureMigrationsTable(): void
    {
        $sql = match ($this->connection->driver()) {
            'mysql' => 'CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL, ran_at DATETIME NOT NULL)',
            'pgsql' => 'CREATE TABLE IF NOT EXISTS migrations (id SERIAL PRIMARY KEY, migration VARCHAR(255) NOT NULL, ran_at TIMESTAMP NOT NULL)',
            default => 'CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration TEXT NOT NULL, ran_at TEXT NOT NULL)',
        };

        $this->connection->statement($sql);
    }
}
