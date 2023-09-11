<?php
namespace App\Model;

use Footup\I18n\Time;
use Footup\Model;

class Article extends Model
{

    protected $casts = [
        'created_at' => Time::class,
    ];
    protected $exclude = ['created_at'];
    protected $beforeInsert = [];
    protected $beforeFind = [];
    protected $beforeDelete = [];
    protected $beforeUpdate = [];
    protected $afterInsert = [];
    protected $afterFind = [];
    protected $afterDelete = [];
    protected $afterUpdate = [];
    protected $hasOne = [
        'user' => [
            'model' => User::class,
            'foreign_key' => 'id_user',
            'local_key' => 'user_id'
        ]
    ];

}