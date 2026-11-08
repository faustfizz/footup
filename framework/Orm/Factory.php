<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| Factory — create models for testing/seeding (minimaliste)
|
| Usage:
|   class UserFactory extends Factory {
|       public function definition() {
|           return [
|               'name' => 'John Doe',
|               'email' => 'john@example.com',
|               'password' => Hash::make('password'),
|           ];
|       }
|   }
|
| Usage: UserFactory::make(10)->create();
|        UserFactory::make()->state(['is_admin' => true])->create();
|--------------------------------------------------------------------------
*/

abstract class Factory
{
    protected string $model = '';
    protected int $count = 1;
    protected array $state = [];

    abstract public function definition(): array;

    public static function make(int $count = 1): static
    {
        $factory = new static();
        $factory->count = $count;

        return $factory;
    }

    public function state(array $attributes): static
    {
        $this->state = array_merge($this->state, $attributes);

        return $this;
    }

    public function create(): Model|array
    {
        if ($this->count === 1) {
            $attributes = array_merge($this->definition(), $this->state);

            return $this->getModel()->create($attributes);
        }

        $models = [];

        for ($i = 0; $i < $this->count; $i++) {
            $attributes = array_merge($this->definition(), $this->state);
            $models[] = $this->getModel()->create($attributes);
        }

        return $models;
    }

    protected function getModel(): Model
    {
        if (!$this->model) {
            $class = str_replace('Factory', '', static::class);

            /** @var class-string<Model> $class */
            return new $class();
        }

        return new $this->model();
    }
}
