<?php

/**
 * Auto generated by Foot Cli
 */

namespace App\Model{name_space};

use Footup\Model;

class {class_name} extends Model{
    /**
     * PrimaryKey
     *
     * @var string
     */
    protected $primaryKey = 'id{append_table}';

    /**
     * Table name
     *
     * @var string
     */
    protected $table = '{table}';

    protected $allow_callbacks = true;

    protected $beforeInsert         = [];
	protected $beforeFind           = [];
	protected $beforeDelete         = [];
	protected $beforeUpdate         = [];
	protected $afterInsert          = [];
	protected $afterFind            = [];
	protected $afterDelete          = [];
	protected $afterUpdate          = [];

}