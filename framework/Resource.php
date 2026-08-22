<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Resource — transform Models to clean JSON
|
| Usage:
|   class UserResource extends Resource {
|       public function toArray() {
|           return ['id' => $this->id, 'name' => $this->name];
|       }
|   }
|   return json(new UserResource($user));
|--------------------------------------------------------------------------
*/

abstract class Resource
{
    public function __construct(protected Model $model)
    {
    }

    abstract public function toArray(): array;

    /**
     * JSON serializes as toArray() result so it's transparent
     * in responses
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __get(string $name): mixed
    {
        return $this->model->{$name};
    }
}
