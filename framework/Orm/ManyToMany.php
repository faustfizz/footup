<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| ManyToMany — attach/detach/sync relationships with a pivot table
|
| Usage in Model:
|   public function roles() {
|       return $this->belongsToMany(
|           Role::class, 
|           'user_roles', 
|           'user_id', 
|           'role_id'
|       );
|   }
|
| In controller:
|   $user->roles()->attach([1, 2, 3]);
|   $user->roles()->detach([1]);
|   $user->roles()->sync([2, 3]);
|   $roles = $user->roles()->get();
|   $roles = $user->roles;  // Lazy-loaded
|   
|   // With scopes on related model!
|   $adminRoles = $user->roles()->query()->active()->get();
|--------------------------------------------------------------------------
*/

class ManyToMany
{
    protected string $pivotTable;
    protected string $foreignKey;
    protected string $relatedKey;
    protected Model $parent;
    protected string $relatedModel;

    public function __construct(
        Model $parent,
        string $relatedModel,
        string $pivotTable,
        string $foreignKey,
        string $relatedKey
    ) {
        $this->parent = $parent;
        $this->relatedModel = $relatedModel;
        $this->pivotTable = $pivotTable;
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
    }

    /**
     * Attach related models (add to pivot table)
     * attach([1, 2, 3]) or attach(1)
     */
    public function attach(int|array $ids, array $pivotData = []): void
    {
        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            $data = array_merge([
                $this->foreignKey => $this->parent->id,
                $this->relatedKey => $id,
            ], $pivotData);

            // Use QueryBuilder via DB table
            DB::table($this->pivotTable)->insert($data);
        }
    }

    /**
     * Detach related models (remove from pivot table)
     * detach([1, 2, 3]) or detach(1) or detach() for all
     */
    public function detach(int|array|null $ids = null): void
    {
        $query = DB::table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->id);

        if ($ids !== null) {
            $ids = is_array($ids) ? $ids : [$ids];
            $query->whereIn($this->relatedKey, $ids);
        }

        $query->delete();
    }

    /**
     * Sync related models (delete old, attach new)
     * sync([1, 2, 3])
     */
    public function sync(array $ids, array $pivotData = []): void
    {
        $this->detach();
        $this->attach($ids, $pivotData);
    }

    /**
     * Get the QueryBuilder for this many-to-many relationship with JOIN
     * Allows chaining scopes and queries on the related model
     *
     * Usage:
     *   $user->roles()->query()->active()->get()
     *   $user->roles()->query()->where('active', true)->get()
     */
    public function query(): ModelQueryBuilder
    {
        $class = $this->relatedModel;
        $table = $class::tableName();

        return $class::query()
            ->join(
                $this->pivotTable,
                "{$table}.id",
                '=',
                "{$this->pivotTable}.{$this->relatedKey}"
            )
            ->where("{$this->pivotTable}.{$this->foreignKey}", $this->parent->id)
            ->select("{$table}.*");
    }

    /**
     * Get all related models
     * Uses the related model's QueryBuilder so scopes work
     */
    public function get(): array
    {
        return $this->query()->get();
    }

    /**
     * Get IDs only
     */
    public function pluck(): array
    {
        return DB::table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->id)
            ->pluck($this->relatedKey);
    }

    /**
     * Count related models
     */
    public function count(): int
    {
        return DB::table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->id)
            ->count();
    }

    /**
     * Check if model is attached
     */
    public function contains(int $id): bool
    {
        return DB::table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->id)
            ->where($this->relatedKey, $id)
            ->exists();
    }
}
