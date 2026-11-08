<?php

declare(strict_types=1);

namespace App\Models;

use Footup\Hashing\Hash;
use Footup\Orm\Model;
use Footup\Orm\Relation;
use Footup\Orm\QueryBuilder;

/*
|--------------------------------------------------------------------------
| User Model — Example with relationships and scopes
|
| Relationships:
|   public function profile(): Relation { return $this->hasOne(...) }
|   public function posts(): Relation { return $this->hasMany(...) }
|   public function roles() { return $this->belongsToMany(...) }
|
| Scopes (instance methods named scope{Name}):
|   public function scopeVerified(QueryBuilder $query): QueryBuilder
|   public function scopeActive(QueryBuilder $query): QueryBuilder
|--------------------------------------------------------------------------
*/

class User extends Model
{
  protected bool $usesTimestamps = true;

  /**
   * One-to-One: User has one Profile
   * Usage: $user->profile() or $user->profile
   */
  public function profile(): Relation
  {
    return $this->hasOne(Profile::class, 'user_id');
  }

  /**
   * One-to-Many: User has many Posts
   * Usage: $user->posts() or $user->posts
   */
  public function posts(): Relation
  {
    return $this->hasMany(Post::class, 'user_id');
  }

  /**
   * Many-to-Many: User has many Roles through user_roles pivot
   * Usage: $user->roles() or $user->roles
   */
  public function roles()
  {
    return $this->belongsToMany(
      Role::class,
      'user_roles',
      'user_id',
      'role_id'
    );
  }

  /**
   * Scope: Get verified users
   * Usage: User::verified()->get()
   */
  public function scopeVerified(QueryBuilder $query): QueryBuilder
  {
    return $query->where('verified', true);
  }

  /**
   * Scope: Get active users
   * Usage: User::active()->get()
   */
  public function scopeActive(QueryBuilder $query): QueryBuilder
  {
    return $query->where('active', true);
  }

  /**
   * Scope: Get users created recently (in last 30 days)
   * Usage: User::recent()->get()
   */
  public function scopeRecent(QueryBuilder $query): QueryBuilder
  {
    return $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-30 days')));
  }

  public function matchPassword(string $password): bool
  {
    return $this->checkPassword($password);
  }

  public function checkPassword(string $password): bool
  {
    return Hash::check($password, $this->password);
  }
}
