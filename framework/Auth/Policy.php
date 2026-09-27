<?php

declare(strict_types=1);

namespace Footup\Auth;

/*
|--------------------------------------------------------------------------
| Policy — resource-based authorization (minimaliste)
|
| Usage:
|   class PostPolicy extends Policy {
|       public function view(Model $user, Post $post) { return true; }
|       public function edit(Model $user, Post $post) { return $user->id === $post->user_id; }
|       public function delete(Model $user, Post $post) { return $user->is_admin; }
|   }
|
| Binding: $app->singleton(PostPolicy::class, PostPolicy::class);
| Usage: Auth::user()->can('edit', $post)
|--------------------------------------------------------------------------
*/

abstract class Policy
{
    /**
     * Check if user can perform action on resource
     * e.g. $user->can('edit', $post)
     */
    public static function can(Model $user, string $action, Model $resource): bool
    {
        $policyClass = static::class;
        $policy = new $policyClass();

        if (!method_exists($policy, $action)) {
            return false;
        }

        return call_user_func([$policy, $action], $user, $resource);
    }
}
