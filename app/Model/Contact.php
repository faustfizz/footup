<?php
namespace App\Model;

use Footup\I18n\Time;
use Footup\Model;

class Contact extends Model
{
    /**
     * PrimaryKey
     *
     * @var string
     */
    protected $primaryKey = 'idcont';

    protected $casts = [
        'created_at' => Time::class
    ];
    protected $beforeInsert = [];
    protected $beforeFind = [];
    protected $beforeDelete = [];
    protected $beforeUpdate = [];
    protected $afterInsert = [];
    protected $afterFind = [];
    protected $afterDelete = [];
    protected $afterUpdate = [];

}