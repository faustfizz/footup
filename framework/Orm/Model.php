<?php

declare(strict_types=1);

namespace Footup\Orm;

use ArrayAccess;
use Footup\Events\Event;
use Footup\Events\Observerable;
use Footup\Html\FormBuilder;

/*
|--------------------------------------------------------------------------
| Minimal Active-Record-ish base. Deliberately small: attributes
| are a plain array behind magic __get/__set, no relationships, no
| mutators/casts, no events — add those as you need them, or graduate
| to a real ORM once a model needs more than CRUD.
|--------------------------------------------------------------------------
*/

abstract class Model implements \JsonSerializable, ArrayAccess
{
  use Observerable, PolymorphicRelationship;

  protected static string $table = '';
  protected string $primaryKey = 'id';

  /** @var array<string, mixed> */
  protected array $attributes = [];
  protected bool $exists = false;
  protected bool $usesTimestamps = true;
  protected bool $usesSoftDeletes = false;
  /** @var array<string, string> Type casts: 'column' => 'integer'|'boolean'|'datetime'|'json'|'float' */
  protected $casts = [];

  /**
   * Get the deleted_at column name
   */
  public function getDeletedAtColumn(): string
  {
    return 'deleted_at';
  }
  /**
   * Mass-assignable attributes (for security)
   * 
   * If defined, only these attributes can be mass-assigned via fill()
   * If empty array [], fill() won't accept any mass-assignment
   * If not defined or ['*'], all attributes can be mass-assigned (legacy mode)
   * 
   * Usage: protected $fillable = ['title', 'body', 'author_id'];
   * @var array<string> $fillable
   */
  protected $fillable = [];

  public function __construct(array $attributes = [])
  {
    $this->fill($attributes);
  }

  public function fill(array $attributes): static
  {
    // Filter attributes if $fillable is defined
    if (!empty($this->fillable) && $this->fillable !== ['*']) {
      // Only allow attributes in $fillable
      $attributes = array_intersect_key($attributes, array_flip($this->fillable));
    }

    foreach ($attributes as $key => $value) {
      $this->attributes[$key] = $value;
    }

    return $this;
  }

  /**
   * Create and save a new model with mass-assignment
   * 
   * Usage: $post = Post::create(['title' => 'Foo', 'body' => 'Bar']);
   * 
   * Note: Respects $fillable - only these attributes can be assigned
   */
  public static function create(array $attributes): static
  {
    $model = new static();
    $model->fill($attributes);
    $model->save();
    return $model;
  }

  public function __get(string $name): mixed
  {
    // Try Convention: getXxxValue() for attribute xxx
    // Example: $user->full_name calls getFullNameValue()
    $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name))) . 'Value';
    if (method_exists($this, $methodName)) {
      return $this->$methodName();
    }

    $value = $this->attributes[$name] ?? null;

    // Apply casts
    if ($value !== null && isset($this->casts[$name])) {
      $value = $this->castValue($value, $this->casts[$name]);
    }

    // If value is null and method exists, call it (lazy-load relationships)
    if ($value === null && method_exists($this, $name)) {
      $result = $this->$name();

      // If result is a Relation, resolve it (call first() or get())
      if ($result instanceof Relation) {
        return $result->resolve();
      }

      return $result;
    }

    return $value;
  }

  public function __set(string $name, mixed $value): void
  {
    // Try Convention: setXxxValue() for attribute xxx
    // Example: $user->password = '...' calls setPasswordValue()
    $methodName = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name))) . 'Value';
    if (method_exists($this, $methodName)) {
      $value = $this->$methodName($value);
      $this->attributes[$name] = $value;
      return;
    }

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
  
  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }

  /**
   * Get the primary key column name
   * Override $primaryKey property to customize
   */
  public function getKeyName(): string
  {
    return $this->primaryKey;
  }

  /**
   * Get the primary key value for this model instance
   */
  public function getKey(): mixed
  {
    return $this->attributes[$this->primaryKey] ?? null;
  }

  /**
   * "BlogPost" -> "blog_posts". Override protected static $table on
   * the model for anything this naive pluralization gets wrong.
   */
  public static function tableName(): string
  {
    if (static::$table !== '') {
      return static::$table;
    }

    $short = (new \ReflectionClass(static::class))->getShortName();
    $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short));

    return $snake . 's';
  }

  public static function query(): ModelQueryBuilder
  {
    return new ModelQueryBuilder(DB::connection(), static::tableName(), static::class);
  }

  /** @return static[] */
  public static function all(): array
  {
    return static::query()->get();
  }

  public static function find(mixed $id): ?static
  {
    return static::query()->where((new static())->primaryKey, $id)->first();
  }

  /**
   * Find by custom column/value and return Model instance
   * Usage: User::firstWhere('email', 'user@example.com')
   */
  public static function firstWhere(string $column, mixed $value): ?static
  {
    return static::query()->where($column, $value)->first();
  }

  /**
   * Find by custom column/value or throw 404
   * Usage: User::firstWhereOrFail('email', 'user@example.com')
   */
  public static function firstWhereOrFail(string $column, mixed $value): static
  {
    $model = static::firstWhere($column, $value);

    if ($model === null) {
      throw new \Footup\Exceptions\NotFoundHttpException(
        static::class . " with {$column} = {$value} not found"
      );
    }

    return $model;
  }

  /**
   * Find by ID or throw 404 exception
   */
  public static function findOrFail(mixed $id): static
  {
    $model = static::find($id);

    if ($model === null) {
      throw new \Footup\Exceptions\NotFoundHttpException(
        static::class . " with ID {$id} not found"
      );
    }

    return $model;
  }

  /**
   * Extract a single column from all results
   * User::pluck('email') → ['john@example.com', 'jane@example.com', ...]
   */
  public static function pluck(string $column): array
  {
    return static::query()->pluck($column);
  }

  /**
   * Process large datasets in chunks
   * User::chunk(100, function($users) {
   *     foreach ($users as $user) {
   *         // Process raw data, not Model instances
   *     }
   * })
   */
  public static function chunk(int $size, callable $callback): bool
  {
    return static::query()->chunk($size, $callback);
  }

  public static function where(string $column, mixed $operatorOrValue, mixed $value = null): ModelQueryBuilder
  {
    return static::query()->where(...func_get_args());
  }

  public function markAsExisting(): static
  {
    $this->exists = true;

    return $this;
  }

  public function save(): static
  {
    if ($this->usesTimestamps) {
      $this->attributes['updated_at'] = date('Y-m-d H:i:s');

      if (!$this->exists) {
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        Event::dispatch(static::class . ':creating', $this);
      } else {
        Event::dispatch(static::class . ':updating', $this);
      }
    }

    if ($this->exists) {
      $id = $this->attributes[$this->primaryKey];
      $data = $this->attributes;
      unset($data[$this->primaryKey]);

      static::query()->where($this->primaryKey, $id)->update($data);
      Event::dispatch(static::class . ':updated', $this);
    } else {
      $id = static::query()->insert($this->attributes);
      $this->attributes[$this->primaryKey] ??= $id;
      $this->exists = true;
      Event::dispatch(static::class . ':created', $this);
    }

    return $this;
  }

  public function delete(): bool
  {
    if (! $this->exists) {
      return false;
    }

    Event::dispatch(static::class . ':deleting', $this);

    if ($this->usesSoftDeletes) {
      $this->attributes[$this->getDeletedAtColumn()] = date('Y-m-d H:i:s');
      $result = (bool) $this->save();
      Event::dispatch(static::class . ':deleted', $this);

      return $result;
    }

    $result = static::query()->where($this->primaryKey, $this->attributes[$this->primaryKey])->delete();

    if ($result) {
      Event::dispatch(static::class . ':deleted', $this);
    }

    return $result;
  }

  /**
   * Permanently delete a soft-deleted record
   */
  public function forceDelete(): bool
  {
    Event::dispatch(static::class . ':forceDeleting', $this);
    $result = static::query()->where($this->primaryKey, $this->attributes[$this->primaryKey])->delete();
    Event::dispatch(static::class . ':forceDeleted', $this);

    return $result;
  }

  /**
   * Restore a soft-deleted record
   */
  public function restore(): bool
  {
    if (!$this->usesSoftDeletes) {
      return false;
    }

    Event::dispatch(static::class . ':restoring', $this);

    $result = (bool) static::query()
      ->where($this->primaryKey, $this->attributes[$this->primaryKey])
      ->update([$this->getDeletedAtColumn() => null]);

    Event::dispatch(static::class . ':restored', $this);

    return $result;
  }

  /**
   * Get only non-deleted records (if using soft deletes)
   */
  public static function withoutTrashed(): ModelQueryBuilder
  {
    $instance = new static();

    if (!$instance->usesSoftDeletes) {
      return static::query();
    }

    return static::query()->whereNull((new static)->getDeletedAtColumn());
  }

  /**
   * Get only soft-deleted records
   */
  public static function onlyTrashed(): ModelQueryBuilder
  {
    $instance = new static();

    if (!$instance->usesSoftDeletes) {
      return static::query();
    }

    return static::query()->whereNotNull((new static)->getDeletedAtColumn());
  }

  /**
   * Include soft-deleted records
   */
  public static function withTrashed(): ModelQueryBuilder
  {
    return static::query();
  }

  /**
   * One-to-one relationship: $user->profile() returns chainable Relation
   * Usage: $user->profile() or $user->profile
   *        $user->profile()->where('verified', true)->first()
   *        $user->profile()->published()->first()  // With scopes
   *        $user->profile->bio (lazy-loaded)
   */
  protected function hasOne(string $modelClass, string $foreignKey = null): Relation
  {
    $foreignKey ??= strtolower((new \ReflectionClass(static::class))->getShortName()) . '_id';
    $id = $this->attributes[$this->primaryKey];

    $builder = $modelClass::where($foreignKey, $id);

    return new Relation('one', $builder, $modelClass);
  }

  /**
   * One-to-many relationship: $user->posts() returns chainable Relation
   * Usage: $user->posts() or $user->posts
   *        $user->posts()->where('published', true)->get()
   *        $user->posts()->published()->recent()->get()  // With scopes
   *        $user->posts (lazy-loaded)
   */
  protected function hasMany(string $modelClass, string $foreignKey = null): Relation
  {
    $foreignKey ??= strtolower((new \ReflectionClass(static::class))->getShortName()) . '_id';
    $id = $this->attributes[$this->primaryKey];

    $builder = $modelClass::where($foreignKey, $id);

    return new Relation('many', $builder, $modelClass);
  }

  /**
   * Many-to-one relationship: $post->author() returns chainable Relation
   * Usage: $post->author() or $post->author
   *        $post->author()->where('verified', true)->first()
   *        $post->author()->verified()->first()  // With scopes
   *        $post->author->name (lazy-loaded)
   */
  protected function belongsTo(string $modelClass, string $foreignKey = null): Relation
  {
    $foreignKey ??= lcfirst((new \ReflectionClass($modelClass))->getShortName()) . '_id';
    $foreignId = $this->attributes[$foreignKey];

    $builder = $modelClass::where('id', $foreignId);

    return new Relation('one', $builder, $modelClass);
  }

  /**
   * Define a local scope — reusable WHERE constraint
   * Usage in Model: $this->scope('active', fn($q) => $q->where('active', true))
   * Usage: User::active()->get()
   */
  protected function scope(string $name, callable $callback): void
  {
    $this->scopes[$name] = $callback;
  }

  /**
   * Dynamic scope lookup for __callStatic
   */
  public static function __callStatic(string $method, array $args): mixed
  {
    $scopeMethod = 'scope' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));

    if (method_exists(static::class, $scopeMethod)) {
      return (new static())->$scopeMethod(static::query(), ...$args);
    }

    // Priority 2: Handle byXxx() - find first model by column
    // User::byEmail('test@example.com') → finds first user with that email
    if (str_starts_with($method, 'by')) {
      $column = self::methodToColumnName(substr($method, 2));

      if (count($args) < 1) {
        throw new \BadMethodCallException("Method [{$method}] requires at least one argument");
      }

      return static::query()->where($column, $args[0])->first();
    }

    // Priority 3: Handle whereXxx() - query builder with where clause
    // User::whereEmail('test@example.com') → ModelQueryBuilder with WHERE email = 'test@example.com'
    if (str_starts_with($method, 'where')) {
      $column = self::methodToColumnName(substr($method, 5));

      if (count($args) < 1) {
        throw new \BadMethodCallException("Method [{$method}] requires at least one argument");
      }

      // Support both simple value and operator + value
      if (count($args) === 1) {
        return static::query()->where($column, $args[0]);
      } else {
        return static::query()->where($column, $args[0], $args[1]);
      }
    }

    $queryBuilder = static::query();
    if (method_exists($queryBuilder, $method)) {
      return call_user_func_array([$queryBuilder, $method], $args);
    }

    throw new \BadMethodCallException("Method [{$method}] not found on " . static::class);
  }

  /**
   * Handle dynamic instance method calls
   * Forwards to ModelQueryBuilder for query operations
   * 
   * Allows:
   *   $post->update(['title' => 'New Title'])
   *   $post->delete()
   *   $post->refresh()
   *
   * @param string $method Method name
   * @param array $arguments Method arguments
   * @return mixed
   * @throws \BadMethodCallException
   */
  public function __call(string $method, array $arguments): mixed
  {
    // Create a query scoped to this model instance
    $query = static::query()->where($this->getKeyName(), $this->getKey());

    // Check if the builder has this method
    if (method_exists($query, $method)) {
      return call_user_func_array([$query, $method], $arguments);
    }

    throw new \BadMethodCallException(
      "Call to undefined method " . static::class . "::{$method}()"
    );
  }

  /**
   * Convert camelCase method name to snake_case column name
   * byFullName → full_name
   * whereUserEmail → user_email
   */
  protected static function methodToColumnName(string $camelCase): string
  {
    return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $camelCase));
  }

  /**
   * Cast a value to its specified type
   */
  protected function castValue(mixed $value, string $type): mixed
  {
    // Check if $type is an Enum class (PHP 8.1+)
    if (PHP_VERSION_ID >= 80100 && class_exists($type) && enum_exists($type)) {
      return $this->castToEnum($value, $type);
    }

    return match ($type) {
      'integer', 'int' => (int) $value,
      'float', 'double' => (float) $value,
      'boolean', 'bool' => (bool) $value,
      'string' => (string) $value,
      'array' => is_array($value) ? $value : json_decode($value, true) ?? [],
      'json' => is_array($value) ? $value : json_decode($value, true) ?? [],
      'datetime', 'timestamp' => $value instanceof \DateTime ? $value : new \DateTime($value),
      'date' => $value instanceof \DateTime ? $value : (\DateTime::createFromFormat('Y-m-d', $value) ?: new \DateTime($value)),
      default => $value,
    };
  }

  public function refresh(): static
  {
    if (!$this->exists) {
      throw new \RuntimeException("Cannot refresh a model that doesn't exist in the database.");
    }

    $fresh = static::find($this->getKey());

    if ($fresh === null) {
      throw new \RuntimeException("Model not found in the database for refresh.");
    }

    $this->attributes = $fresh->attributes;
    return $this;
  }

  /**
   * Cast value to an Enum instance
   * Supports both BackedEnum (with values) and PureEnum (without values)
   */
  protected function castToEnum(mixed $value, string $enumClass): mixed
  {
    // Already an enum instance of this type
    if ($value instanceof $enumClass) {
      return $value;
    }

    // BackedEnum (has values like 'pending', 1, etc)
    if (method_exists($enumClass, 'tryFrom')) {
      $enum = $enumClass::tryFrom($value);
      if ($enum !== null) {
        return $enum;
      }
    }

    // PureEnum (no values, just names)
    if (method_exists($enumClass, 'cases')) {
      foreach ($enumClass::cases() as $case) {
        if ($case->name === $value || $case->value === $value) {
          return $case;
        }
      }
    }

    // Return original value if no match
    return $value;
  }

  /**
   * Define a many-to-many relationship through a pivot table
   */
  protected function belongsToMany(
    string $relatedModel,
    string $pivotTable,
    string $foreignKey,
    string $relatedKey
  ): ManyToMany {
    return new ManyToMany(
      $this,
      $relatedModel,
      $pivotTable,
      $foreignKey,
      $relatedKey
    );
  }

  /**
   * Create a FormBuilder for this model (static)
   * Usage: Post::form()->render()
   *        Post::form()->field('title')->label('Post Title')->render()
   */
  public static function form(): FormBuilder
  {
    return new FormBuilder(static::class);
  }

  /**
   * Check if this model uses soft deletes
   */
  public function isUsingSoftDeletes(): bool
  {
      return $this->usesSoftDeletes;
  }

  /**
   * Create a FormBuilder for this model instance
   * Usage: $post->form()->render()  // Pre-fills with $post data
   */
  public function formFor(): FormBuilder
  {
    return new FormBuilder(static::class, $this);
  }
  
  public function offsetExists(mixed $offset): bool
  {
    return isset($this->attributes[$offset]);
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->attributes[$offset] ?? null;
  }
  
  public function offsetSet(mixed $offset, mixed $value): void
  {
    if ($offset === null) {
      throw new \InvalidArgumentException("Offset cannot be null");
    }
    $this->attributes[$offset] = $value;
  }

  public function offsetUnset(mixed $offset): void
  {
    unset($this->attributes[$offset]);
  }
}
