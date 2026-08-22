<?php

declare(strict_types=1);

namespace Footup\Database;

use Footup\Pagination;

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

    public function __construct(protected Connection $connection, protected string $table)
    {
    }

    public function select(string ...$columns): self
    {
        $this->columns = $columns ?: ['*'];

        return $this;
    }

    /**
     * where('name', 'Ada')            -> name = ?
     * where('votes', '>', 10)         -> votes > ?
     */
    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        if (func_num_args() === 2) {
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

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table . $this->whereClause();

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

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS aggregate FROM ' . $this->table . $this->whereClause();
        $row = $this->connection->selectOne($sql, $this->bindings);

        return (int) ($row['aggregate'] ?? 0);
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

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}" . $this->whereClause();

        return $this->connection->statement($sql, $this->bindings);
    }

    /** @return array{data: array, total: int, per_page: int, current_page: int, last_page: int} */
    public function paginate(int $perPage = 15, ?Pagination $pagination = null): array
    {
        $pagination ??= Pagination::fromRequest($perPage);
        $total = $this->count();

        $this->limit($perPage)->offset($pagination->offset());
        $data = $this->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $pagination->currentPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }
}
