<?php

namespace Footup\Auth;

use Footup\Orm\Model;

class Authenticable extends Model
{
  protected static string $table = 'users';
  protected string $primaryKey = 'id';

}
