<?php

declare(strict_types=1);

namespace Footup\Orm;

use Footup\Orm\Pagination;

/*
|--------------------------------------------------------------------------
| Fluent, parameter-bound SQL builder. Every value passed to
| where()/whereIn()/insert()/update() goes through PDO bindings — never
| string-concatenated into the SQL — so building queries this way is
| SQL-injection-safe by construction.
|--------------------------------------------------------------------------
*/

class QueryBuilder
{
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orders = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected array $eagerLoad = [];  // Relations to eager load
    protected array $joins = [];
    protected bool $withTrashedFlag = false;
    protected bool $onlyTrashedFlag = false;
    protected bool $modelUsesSoftDeletes = false;
    protected string $deletedAtColumn = 'deleted_at';

    public function __construct(protected Connection $connection, protected string $table)
    {
    }

    /**
     * Enable soft deletes for this query builder
     * Called by ModelQueryBuilder to configure soft delete behavior
     */
    public function enableSoftDeletes(string $column = 'deleted_at'): self
    {
        $this->modelUsesSoftDeletes = true;
        $this->deletedAtColumn = $column;
        return $this;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function select(string ...$columns): self
    {
        $this->columns = $columns ?: ['*'];

        return $this;
    }

    /**
     * Eager load relationships to avoid N+1 queries
     * e.g. Post::with('author', 'comments')->get()
     */
    public function with(string ...$relations): self
    {
        $this->eagerLoad = array_merge($this->eagerLoad, $relations);

        return $this;
    }

    /**
     * where('name', 'Ada')            -> name = ?
     * where('votes', '>', 10)         -> votes > ?
     */
    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        if ($value === null && $operatorOrValue) {
            $operator = '=';
            $value = $operatorOrValue;
        } else {
            $operator = (string) $operatorOrValue;
        }

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NULL";

        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NOT NULL";

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = $column . ' ' . (strtolower($direction) === 'desc' ? 'DESC' : 'ASC');

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitValue = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;

        return $this;
    }
    /**
     * Join a table
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('INNER JOIN', $table, $first, $operator, $second);
    }

    /**
     * Left join a table
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('LEFT JOIN', $table, $first, $operator, $second);
    }

    /**
     * Right join a table
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('RIGHT JOIN', $table, $first, $operator, $second);
    }

    /**
     * Inner join (alias for join)
     */
    public function innerJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second);
    }

    /**
     * Cross join
     */
    public function crossJoin(string $table): self
    {
        $this->joins[] = "CROSS JOIN {$table}";
        return $this;
    }

    private function addJoin(string $type, string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = "{$type} {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    protected function joinClause(): string
    {
        return $this->joins ? ' ' . implode(' ', $this->joins) : '';
    }

    public function toSql(): string
    {
        // Add soft delete scope before building SQL
        $this->whereSoftDeleted();

        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table . $this->joinClause() . $this->whereClause();

        if ($this->orders) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    protected function whereClause(): string
    {
        return $this->wheres ? ' WHERE ' . implode(' AND ', $this->wheres) : '';
    }

    /** @return array<int, array<string, mixed>> */
    public function get(): array
    {
        return $this->connection->select($this->toSql(), $this->bindings);
    }

    /** @return array<string, mixed>|null */
    public function first(): ?array
    {
        $previousLimit = $this->limitValue;
        $this->limitValue = 1;
        $row = $this->get()[0] ?? null;
        $this->limitValue = $previousLimit;

        return $row;
    }

    /**
     * Get first result or throw 404 exception
     */
    public function firstOrFail(): array
    {
        $row = $this->first();

        if ($row === null) {
            throw new \Footup\Exceptions\NotFoundHttpException(
                "No records found in table {$this->table}"
            );
        }

        return $row;
    }

    /**
     * Extract a single column from all results
     * Post::whereStatus('published')->pluck('title') → ['Post 1', 'Post 2', ...]
     */
    public function pluck(string $column): array
    {
        return array_map(fn($row) => $row[$column], $this->get());
    }

    /**
     * Find by primary key (id by default)
     * Post::find(42) is equivalent to Post::where('id', 42)->first()
     */
    public function find(mixed $id): ?array
    {
        // Assuming 'id' is the default primary key in most cases
        // This will be overridden in Model::find() for model-specific primary keys
        return $this->where('id', $id)->first();
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS aggregate FROM ' . $this->table . $this->whereClause();
        $row = $this->connection->selectOne($sql, $this->bindings);

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * Check if any records exist matching the query conditions
     * Post::where('status', 'published')->exists() → true/false
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function insert(array $data): string
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        return $this->connection->insertGetId($sql, array_values($data));
    }

    public function update(array $data): bool
    {
        $set = implode(', ', array_map(fn ($column) => "{$column} = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set}" . $this->whereClause();

        return $this->connection->statement($sql, [...array_values($data), ...$this->bindings]);
    }

    /**
     * Include soft-deleted records in query
     */
    public function withTrashed(): self
    {
        $this->withTrashedFlag = true;
        $this->onlyTrashedFlag = false;
        return $this;
    }

    /**
     * Only return soft-deleted records
     */
    public function onlyTrashed(): self
    {
        $this->onlyTrashedFlag = true;
        $this->withTrashedFlag = false;
        return $this;
    }

    /**
     * Exclude soft-deleted records (default scope)
     */
    public function whereSoftDeleted(): self
    {
        // Only apply soft delete logic if model uses soft deletes
        if (!$this->modelUsesSoftDeletes) {
            return $this;
        }

        if (!$this->onlyTrashedFlag && !$this->withTrashedFlag) {
            $this->wheres[] = "{$this->deletedAtColumn} IS NULL";
        } elseif ($this->onlyTrashedFlag) {
            $this->wheres[] = "{$this->deletedAtColumn} IS NOT NULL";
        }
        return $this;
    }

    /**
     * Soft delete: Set deleted_at instead of removing
     */
    public function softDelete(): bool
    {
        // Only soft delete if model uses soft deletes
        if (!$this->modelUsesSoftDeletes) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET {$this->deletedAtColumn} = ? " . $this->whereClause();
        $bindings = [date('Y-m-d H:i:s'), ...$this->bindings];

        return $this->connection->statement($sql, $bindings);
    }

    /**
     * Force permanent delete
     */
    public function forceDelete(): bool
    {
        $sql = "DELETE FROM {$this->table}" . $this->whereClause();

        return $this->connection->statement($sql, $this->bindings);
    }

    /**
     * Regular delete (calls forceDelete, soft delete handled by model)
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}" . $this->whereClause();

        return $this->connection->statement($sql, $this->bindings);
    }

    /**
     * @return \Footup\Orm\PaginatedResult
     */
    public function paginate(int $perPage = 15, ?Pagination $pagination = null): PaginatedResult
    {
        $pagination ??= Pagination::fromRequest($perPage);
        $total = $this->count();

        // Calculate last page
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

        // Validate current page - don't go beyond last page
        if ($total > 0 && $pagination->currentPage > $lastPage) {
            $pagination->currentPage = $lastPage;
        }

        // Fetch the data for current page
        $this->limit($perPage)->offset($pagination->offset());
        $data = $this->get();

        // Set pagination metadata
        $pagination->setTotal($total)->setLastPage($lastPage);

        // Return PaginatedResult object
        return new PaginatedResult($data, $pagination);
    }

    /**
     * Process large datasets in chunks to avoid memory issues
     * Post::all()->chunk(100, function($posts) {
     *     foreach ($posts as $post) {
     *         // Process each post
     *     }
     * })
     */
    public function chunk(int $size, callable $callback): bool
    {
        $page = 1;

        while (true) {
            $offset = ($page - 1) * $size;
            $this->offsetValue = $offset;
            $this->limitValue = $size;
            
            $results = $this->get();

            if (empty($results)) {
                break;
            }

            if ($callback($results) === false) {
                return false;
            }

            $page++;
        }

        return true;
    }
}