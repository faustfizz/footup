<?php
namespace App\Model;
use Footup\Model;

class Contact extends Model{
    /**
     * PrimaryKey
     *
     * @var string
     */
    protected $primaryKey = 'idcont';

    protected $beforeInsert         = [];
	protected $beforeFind           = [];
	protected $beforeDelete         = [];
	protected $beforeUpdate         = [];
	protected $afterInsert          = [];
	protected $afterFind            = [];
	protected $afterDelete          = [];
	protected $afterUpdate          = [];

}