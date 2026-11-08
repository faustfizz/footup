<?php

declare(strict_types=1);

namespace Footup\Events;

use Footup\Orm\Model;

/*
|--------------------------------------------------------------------------
| Observer — model lifecycle hooks (minimaliste)
|
| Usage:
|   class UserObserver extends Observer {
|       public function creating(Model $model) { ... }
|       public function created(Model $model) { ... }
|       public function updating(Model $model) { ... }
|       public function updated(Model $model) { ... }
|       public function deleting(Model $model) { ... }
|       public function deleted(Model $model) { ... }
|   }
|--------------------------------------------------------------------------
*/

class Observer
{
    public function creating(Model $model): void
    {
    }

    public function created(Model $model): void
    {
    }

    public function updating(Model $model): void
    {
    }

    public function updated(Model $model): void
    {
    }

    public function deleting(Model $model): void
    {
    }

    public function deleted(Model $model): void
    {
    }
}
