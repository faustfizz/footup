<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Minimal Active-Record-ish base. Deliberately small: attributes
| are a plain array behind magic __get/__set, no relationships, no
| mutators/casts, no events — add those as you need them, or graduate
| to a real ORM once a model needs more than CRUD.
|--------------------------------------------------------------------------
*/

abstract class Model
{
    protected static string $table = '';
    protected string $primaryKey = 'id';

    /** @var array<string, mixed> */
    protected array $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * "BlogPost" -> "blog_posts". Override protected static $table on
     * the model for anything this naive pluralization gets wrong.
     */
    protected static function tableName(): string
    {
        if (static::$table !== '') {
            return static::$table;
        }

        $short = (new \ReflectionClass(static::class))->getShortName();
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short));

        return $snake . 's';
    }

    public static function query(): QueryBuilder
    {
        return DB::table(static::tableName());
    }

    /** @return static[] */
    public static function all(): array
    {
        return array_map(
            fn (array $row) => (new static($row))->markAsExisting(),
            static::query()->get()
        );
    }

    public static function find(mixed $id): ?static
    {
        $row = static::query()->where((new static())->primaryKey, $id)->first();

        return $row ? (new static($row))->markAsExisting() : null;
    }

    public static function where(string $column, mixed $operatorOrValue, mixed $value = null): QueryBuilder
    {
        return static::query()->where(...func_get_args());
    }

    protected function markAsExisting(): static
    {
        $this->exists = true;

        return $this;
    }

    public function save(): static
    {
        if ($this->exists) {
            $id = $this->attributes[$this->primaryKey];
            $data = $this->attributes;
            unset($data[$this->primaryKey]);

            static::query()->where($this->primaryKey, $id)->update($data);
        } else {
            $id = static::query()->insert($this->attributes);
            $this->attributes[$this->primaryKey] ??= $id;
            $this->exists = true;
        }

        return $this;
    }

    public function delete(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return static::query()->where($this->primaryKey, $this->attributes[$this->primaryKey])->delete();
    }

    /**
     * One-to-many relationship: $user->posts() returns all Post where post.user_id = user.id
     * Usage: new static()->hasMany(Post::class, 'user_id')
     */
    protected function hasMany(string $modelClass, string $foreignKey = null): array
    {
        $foreignKey ??= strtolower((new \ReflectionClass(static::class))->getShortName()) . '_id';
        $id = $this->attributes[$this->primaryKey];

        return $modelClass::where($foreignKey, $id)->get();
    }

    /**
     * Many-to-one relationship: $post->author() returns User where user.id = post.user_id
     * Usage: new static()->belongsTo(User::class, 'user_id')
     */
    protected function belongsTo(string $modelClass, string $foreignKey = null): ?object
    {
        $foreignKey ??= lcfirst((new \ReflectionClass($modelClass))->getShortName()) . '_id';
        $foreignId = $this->attributes[$foreignKey];

        return $modelClass::find($foreignId);
    }
}
