<?php
namespace App\Model;

use Footup\Model;

class User extends Model
{
    protected $belongTo = [
        'article' => [
            'model' => Article::class,
            'foreign_key' => 'user_id',
            'local_key' => 'id_user'
        ]
    ];

}