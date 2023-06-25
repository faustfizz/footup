<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm\Traits;

use Footup\Orm\BaseModel;

trait Relations
{
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

    /**
     * Load the defined relation models
     * and add them as property of this model.
     *
     * @return BaseModel|BaseModel[]|null
     */
    public function loadRelations($for, $limit = null, $offset = null)
    {
        if (count($this->hasOne) && isset($this->hasOne[$for])) {
            $relation = $this->hasOne[$for];
            return $this->{$for} = $this->hasOne($relation);
        }

        if (count($this->hasMany) && isset($this->hasMany[$for])) {
            $relation = $this->hasMany[$for];
            return $this->{$for} = $this->hasMany($relation, $limit, $offset);
        }

        if (count($this->belongsTo) && isset($this->belongsTo[$for])) {
            $relation = $this->belongsTo[$for];
            return $this->{$for} = $this->belongsTo($relation);
        }

        if (count($this->belongsToMany) && isset($this->belongsToMany[$for])) {
            $relation = $this->belongsToMany[$for];
            return $this->{$for} = $this->belongsToMany($relation, $limit, $offset);
        }

        if (count($this->manyMany) && isset($this->manyMany[$for])) {
            $relation = $this->manyMany[$for];
            return $this->{$for} = $this->manyMany($relation, $limit, $offset);
        }
        
    }

    /**
     * Get object in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel Model instance
     */
    protected function hasOne($relationConfig)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        return $class->where($foreign_key, $this->{$local_key})->one();
    }

    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    protected function manyMany($relationConfig, $limit = null, $offset = null)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();

        /**
         * @var BaseModel
         */
        $pivot = new $relationConfig['pivot']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];
        $pivot_foreign_key = $relationConfig['pivot_foreign_key'];
        $pivot_local_key = $relationConfig['pivot_local_key'];

        return $class->join($pivot->getTable()." pivot", "pivot.$pivot_foreign_key = ".$class->getTable().".$foreign_key")
                        ->join($this->table, $this->table.".$local_key = pivot.$pivot_local_key")
                        ->get($class->getTable().".*, pivot.*", "pivot.$pivot_local_key = ".$this->{$local_key}, $limit, $offset);
    }

    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    protected function hasMany($relationConfig, $limit = null, $offset = null)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        return $class->get("*", [$foreign_key => $this->{$local_key}], $limit, $offset);
    }

    /**
     * Get object in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel Model instance
     */
    protected function belongsTo($relationConfig)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        return $class->where($foreign_key, $this->{$local_key})->one();
    }

    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    protected function belongsToMany($relationConfig, $limit = null, $offset = null)
    {
        /**
         * @var BaseModel
         */
        $model = new $relationConfig['model']();
        
        /**
         * @var BaseModel
         */
        $pivot = new $relationConfig['pivot']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];
        $pivot_foreign_key = $relationConfig['pivot_foreign_key'];
        $pivot_local_key = $relationConfig['pivot_local_key'];

        return $model->join($pivot->getTable()." pivot", "pivot.$pivot_foreign_key = ".$model->getTable().".$foreign_key")
                        ->join($this->table, $this->table.".$local_key = pivot.$pivot_local_key")
                        ->get($model->getTable().".*, pivot.*", "pivot.$pivot_local_key = ".$this->{$local_key}, $limit, $offset);
    }

}