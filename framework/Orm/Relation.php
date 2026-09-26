<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| Relation — Wraps ModelQueryBuilder for chainable relationship queries
|
| Supports:
|   - ModelQueryBuilder methods: $user->posts()->where('published', true)->get()
|   - Model scopes: $user->posts()->published()->recent()->get()
|   - Lazy-loading: $user->posts (auto-calls get())
|
| Scopes are instance methods named scope{Name}:
|   public function scopePublished(ModelQueryBuilder $query): ModelQueryBuilder
|   public function scopeRecent(ModelQueryBuilder $query): ModelQueryBuilder
|--------------------------------------------------------------------------
*/

class Relation
{
    protected string $cardinality; // 'one' or 'many'
    protected ModelQueryBuilder $builder;
    protected string $modelClass; // Related model class for scope delegation

    /**
     * @param string $cardinality 'one' or 'many'
     * @param ModelQueryBuilder $builder The ModelQueryBuilder for this relation
     * @param string $modelClass The related model class (for scope delegation)
     */
    public function __construct(string $cardinality, ModelQueryBuilder $builder, string $modelClass)
    {
        $this->cardinality = $cardinality;
        $this->builder = $builder;
        $this->modelClass = $modelClass;
    }

    /**
     * Get cardinality (one or many)
     */
    public function getCardinality(): string
    {
        return $this->cardinality;
    }

    /**
     * Get the underlying ModelQueryBuilder
     */
    public function getBuilder(): ModelQueryBuilder
    {
        return $this->builder;
    }

    /**
     * Proxy method calls to ModelQueryBuilder, then try Model scopes
     *
     * Allows:
     *   $user->posts()->where('published', true)        // ModelQueryBuilder
     *   $user->posts()->published()->recent()           // Model scopes (scopeXxx methods)
     */
    public function __call(string $method, array $args): mixed
    {
        // Try ModelQueryBuilder method first
        if (method_exists($this->builder, $method)) {
            $result = $this->builder->$method(...$args);
            // If result is ModelQueryBuilder, wrap back in Relation for chaining
            if ($result instanceof ModelQueryBuilder) {
                return new self($this->cardinality, $result, $this->modelClass);
            }
            return $result;
        }

        // Try Model scope (scope{Name} method on related model)
        $scopeMethod = 'scope' . ucfirst($method);
        if (method_exists($this->modelClass, $scopeMethod)) {
            $instance = new $this->modelClass();
            $result = $instance->$scopeMethod($this->builder, ...$args);
            // If result is ModelQueryBuilder, wrap back in Relation for chaining
            if ($result instanceof ModelQueryBuilder) {
                return new self($this->cardinality, $result, $this->modelClass);
            }
            return $result;
        }

        throw new \BadMethodCallException(
            "Method [{$method}] or scope [{$scopeMethod}] not found on ModelQueryBuilder or {$this->modelClass}"
        );
    }

    /**
     * Lazy-load: materialize the relationship
     * Called automatically when accessing as property via Model.__get()
     */
    public function resolve(): mixed
    {
        if ($this->cardinality === 'one') {
            return $this->builder->first();
        }

        return $this->builder->get();
    }
}
