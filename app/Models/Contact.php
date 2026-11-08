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

class Contact extends Model
{
  protected static string $table = 'contact';
  protected string $primaryKey = 'idcont';
  protected bool $usesTimestamps = false;
}
