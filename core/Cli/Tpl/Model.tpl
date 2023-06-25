<?php

/**
 * Auto generated by Foot Cli
 */

namespace {name_space};

use Footup\I18n\Time;
use Footup\Model;

class {class_name} extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = '{table}';
    
    /**
     * PrimaryKey
     *
     * @var string
     */
    protected $primaryKey = '{primary_key}';

    /**
     * ReturnType
     *  self or object or array -- default to self
     * @var string
     */
    protected $returnType = '{return_type}';

    /**
     * Array with keys as columns and values as types to cast when reading the column
     * For now, we use casting just for accessing
     *
     * @var array
     */
    protected $casts    = [
        'updated_at'    =>  Time::class,
        'created_at'    =>  Time::class,
    ];

    /**
     * Add all fillable fields here, if empty, all fields are fillable except  fields added on the **exclude** array
     * 
     * @var string[]
     */
    protected $fillable         = [];

    /**
     * Add all non fillable fields here, if empty, all fields are fillable
     * 
     * Consider using this in case you have too many fields and cannot add them all on **fillable** array
     * 
     * @var string[]
     */
    protected $exclude         = [];

    /**
     * FRelationships
     *
     * ``` 
     * <?php
     * # Use with arrays:
     *
     *      protected $hasOne = [
     *           'properties1' => [
     *                              'model' => 'Other_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     * ```
     */
    protected $hasOne        = [];

    /**
     * FRelationships
     *
     * ```
     * <?php
     * # Use with arrays:
     * 
     *      protected $hasMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     * ```
     */
    protected $hasMany       = [];

    /**
     * FRelationships
     *
     * ```
     * <?php
     * # Use with arrays:
     *
     *      protected $manyMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model',
     *                              'pivot' => 'Pivot_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field',
     *                              'pivot_foreign_key' => 'modelKey_in_pivot_table',
     *                              'pivot_local_key' => 'localKey_in_pivot_table',
     *                             ]
     *          ....................
     *      ];
     * ```
     *
     */
    protected $manyMany      = [];

    /**
     * FRelationships
     *
     * ```
     * <?php
     * # Use with arrays:
     *
     *     protected $belongsTo = [
     *           'properties1' => [
     *                              'model' => 'Other_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     * ```
     */
    protected $belongsTo     = [];

    /**
     * FRelationships
     *
     * ```
     * <?php
     * # Use with arrays:
     * 
     *      protected $belongsToMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model',
     *                              'pivot' => 'Pivot_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field',
     *                              'pivot_foreign_key' => 'modelKey_in_pivot_table',
     *                              'pivot_local_key' => 'localKey_in_pivot_table',
     *                             ]
     *          ....................
     *      ];
     * ```
     */
    protected $belongsToMany = [];

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