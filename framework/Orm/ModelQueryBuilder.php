<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| ModelQueryBuilder — Composition wrapper autour QueryBuilder
|--------------------------------------------------------------------------
*/

class ModelQueryBuilder
{
  private QueryBuilder $builder;
  /**
   * @var class-string<Model> $modelClass
   */
  private $modelClass;

  public function __construct(Connection $connection, string $table, string $modelClass)
  {
    $this->builder = new QueryBuilder($connection, $table);
    $this->modelClass = $modelClass;

    // Configure soft deletes if the model uses them
    $modelInstance = new $modelClass();
    /** @var Model $modelInstance */
    if ($modelInstance->isUsingSoftDeletes() ?? false) {
      $this->builder->enableSoftDeletes($modelInstance->getDeletedAtColumn());
    }
  }

  /**
   * Delegate to builder and convert results
   */
  public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
  {
    $this->builder->where($column, $operatorOrValue, $value);
    return $this;
  }

  public function whereIn(string $column, array $values): self
  {
    $this->builder->whereIn($column, $values);
    return $this;
  }

  public function whereNull(string $column): self
  {
    $this->builder->whereNull($column);
    return $this;
  }

  public function whereNotNull(string $column): self
  {
    $this->builder->whereNotNull($column);
    return $this;
  }

  public function orderBy(string $column, string $direction = 'asc'): self
  {
    $this->builder->orderBy($column, $direction);
    return $this;
  }

  public function limit(int $limit): self
  {
    $this->builder->limit($limit);
    return $this;
  }

  public function offset(int $offset): self
  {
    $this->builder->offset($offset);
    return $this;
  }

  public function withTrashed(): self
  {
    $this->builder->withTrashed();
    return $this;
  }

  public function onlyTrashed(): self
  {
    $this->builder->onlyTrashed();
    return $this;
  }

  public function insert(array $data): string
  {
    $columns = array_keys($data);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO {$this->modelClass::tableName()} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

    return $this->builder->getConnection()->insertGetId($sql, array_values($data));
  }

  /**
   * Get all results as Models
   */
  public function get(): array
  {
    $rows = $this->builder->get();

    if (!$this->modelClass || empty($rows)) {
      return $rows;
    }

    return array_map(
      fn(array $row) => (new $this->modelClass($row))->markAsExisting(),
      $rows
    );
  }

  /**
   * Get first result as Model
   */
  public function first(): mixed
  {
    $row = $this->builder->first();

    if (empty($row)) {
      return $row;
    }

    return (new $this->modelClass($row))
      ->markAsExisting();
  }

  /**
   * Get first result or throw 404
   */
  public function firstOrFail(): mixed
  {
    $row = $this->builder->firstOrFail();

    return (new $this->modelClass($row))
      ->markAsExisting();
  }

  /**
   * Get results with pagination
   */
  public function paginate(int $perPage = 15, ?Pagination $pagination = null): PaginatedResult
  {
    $result = $this->builder->paginate($perPage, $pagination);

    $models = array_map(
      fn(array $row) => (new $this->modelClass($row))->markAsExisting(),
      $result->data()
    );

    return new PaginatedResult($models, $result->pagination());
  }

  /**
   * Handle dynamic method calls for model scopes
   * 
   * Allows calling scope methods defined on the model
   * 
   * Usage:
   *   class User extends Model {
   *       public function scopeActive(ModelQueryBuilder $query) {
   *           return $query->where('active', true);
   *       }
   *   }
   *   
   *   User::active()->get();
   *   User::where('email', 'test@example.com')->active()->get();
   *
   * @param string $method Method name (without 'scope' prefix)
   * @param array $arguments Method arguments
   * @return ModelQueryBuilder|mixed
   * @throws \BadMethodCallException
   */
  public function __call(string $method, array $arguments)
  {
      // Check if model has a scope for this method
      $scopeMethod = 'scope' . ucfirst($method);

      if (method_exists($this->modelClass, $scopeMethod)) {
          // Create a model instance to call the scope on
          $model = new $this->modelClass();
          return call_user_func_array([$model, $scopeMethod], [$this, ...$arguments]);
      }

      // Try delegating to the underlying QueryBuilder
      if (method_exists($this->builder, $method)) {
          $result = call_user_func_array([$this->builder, $method], $arguments);

          // If result is a QueryBuilder, wrap it back in ModelQueryBuilder for chaining
          if ($result instanceof QueryBuilder && !$result instanceof ModelQueryBuilder) {
              $this->builder = $result;
              return $this;
          }

          return $result;
      }

      throw new \BadMethodCallException("Call to undefined method " . static::class . "::{$method}()");
  }

  /**
   * Process results in chunks
   */
  public function chunk(int $size, callable $callback): bool
  {
    $page = 1;

    while (true) {
      $this->builder->offset(($page - 1) * $size)->limit($size);
      $results = $this->builder->get();

      if (empty($results)) {
        break;
      }

      $results = array_map(
        fn(array $row) => (new $this->modelClass($row))->markAsExisting(),
        $results
      );

      if ($callback($results) === false) {
        return false;
      }

      $page++;
    }

    return true;
  }

  /**
   * Count results
   */
  public function count(): int
  {
    return $this->builder->count();
  }

  /**
   * Check if any results exist
   */
  public function exists(): bool
  {
    return $this->builder->exists();
  }

  /**
   * Pluck a column
   */
  public function pluck(string $column): array
  {
    return $this->builder->pluck($column);
  }

  /**
   * Update records
   */
  public function update(array $data): bool
  {
    return $this->builder->update($data);
  }

  /**
   * Delete records
   */
  public function delete(): bool
  {
    return $this->builder->delete();
  }

  /**
   * Force delete (permanent)
   */
  public function forceDelete(): bool
  {
    return $this->builder->forceDelete();
  }

  /**
   * Soft delete
   */
  public function softDelete(): bool
  {
    return $this->builder->softDelete();
  }

  public function join(string $table, string $first, string $operator, string $second): self
  {
    $this->builder->join($table, $first, $operator, $second);
    return $this;
  }

  public function leftJoin(string $table, string $first, string $operator, string $second): self
  {
    $this->builder->leftJoin($table, $first, $operator, $second);
    return $this;
  }

  public function rightJoin(string $table, string $first, string $operator, string $second): self
  {
    $this->builder->rightJoin($table, $first, $operator, $second);
    return $this;
  }

  public function innerJoin(string $table, string $first, string $operator, string $second): self
  {
    $this->builder->innerJoin($table, $first, $operator, $second);
    return $this;
  }

  public function crossJoin(string $table): self
  {
    $this->builder->crossJoin($table);
    return $this;
  }

  /**
   * Get raw SQL
   */
  public function toSql(): string
  {
    return $this->builder->toSql();
  }
}