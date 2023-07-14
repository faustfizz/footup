<?php

/**
 * FOOTUP - FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use Footup\Database\DbConnection;
use Footup\Database\QueryBuilder;
use PDO;
use PDOStatement;

class ModelQueryBuilder extends QueryBuilder
{
    /**
     * @var string
     */
    protected $returnType;

    /**
     * @var \PDO $db connection
     */
    protected static $db = null;

    /**
     * @var string|object $model classe courrante
     */
    protected $model;

    /**
     * QueryBuilder constructor
     *
     * @param BaseModel $model the model that use the query builder
     * @param PDO $DbConnection
     */
    public function __construct(BaseModel $model, $DbConnection = null)
    {
        $this->model = $model;
        self::$db = $DbConnection instanceof PDO ? $DbConnection : DbConnection::getDb(true);

        $this->getTable();
        $this->getPrimaryKey();
        $this->getReturnType();

        parent::__construct($this->getTable(), self::$db);
    }

    /*** Core Methods ***/

    /**
     * Builds a delete query.
     *
     * @param scalar $where Where conditions
     * @return bool
     */
    public function delete($where = null)
    {
        if($this->model->id() && empty($where))
        {
            return parent::delete($this->getPrimaryKey()." = ".$this->model->id());
        }
        return parent::delete($where);
    }
    
    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * @return mixed Row value
     */
    public function value($name)
    {
        $returnType = $this->getReturnType();

        if($this->getReturnType() !== 'object') {
            $this->setReturnType('object');
        }
        
        $row = $this->one();

        $this->setReturnType($returnType);

        return (!empty($row)) ? $row->$name : null;
    }

    /**
     * @inheritDoc 
     */
    public function find($value = [], $field = null)
    {
        $field = is_null($field) ? $this->getPrimaryKey() : $field;

        if (!empty($value)) {
            if ((is_int($value) || is_string($value)) && ($this->getPrimaryKey() == $field || isset($this->model->getFieldNames()[$field]))) {
                $this->where($field, $value);
            } else if (is_array($value)) {
                $this->whereIn($field, $value);
            }
        }

        if (empty($this->sql)) {
            $this->select();
        }

        return $field == $this->getPrimaryKey() && is_array($value) ? $this->get() : $this->one($field);
    }

    /**
     * @inheritDoc
     * @return Collection<int, BaseModel|array|object>
     */
    public function get($select = "*", $where = null, $limit = null, $offset = null)
    {
        if (!empty($where)) {
            $this->where($where);
        }
        if (empty($this->sql)) {
            $this->select($select, $limit, $offset);
        }

        $this->sql(array(
            'SELECT',
            $this->distinct,
            $this->selectFields,
            'FROM',
            ($this->getTable()),
            $this->joins,
            $this->where,
            $this->groups,
            $this->having,
            $this->order,
            $this->limit,
            $this->offset
        ));

        $execute = $this->execute();

        /**
         * @var \PDOStatement
         */
        $result = $execute->result;
        $items = [];

        switch ($this->returnType) {
            case 'object':
                $items = $result->fetchAll(PDO::FETCH_OBJ);
                break;
            
            case 'array':
                $items = $result->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'self':
                default:{
                    $items = $result->fetchAll(PDO::FETCH_ASSOC);
                    $model = get_class($this->model);
                    $items =  array_map(function ($item) use ($model) {
                        return new $model($item);
                    }, $items);
                }
        }
        return new Collection($items);

    }

    /**
     * Get the table name for this ER class.
     * 
     * @access public
     * @return string
     */
    public function getTable()
    {
        if(empty($this->table)) $this->table = $this->model->getTable();

        return $this->table;
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        if(empty($this->primaryKey)) $this->primaryKey = $this->model->getPrimaryKey();

        parent::setPrimaryKey($this->primaryKey);
        
        return $this->primaryKey;
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getReturnType()
    {
        if(empty($this->returnType)) $this->returnType = $this->model->getReturnType();
        
        return $this->returnType;
    }

    /**
     * Get $model classe courrante
     *
     * @return  string|object
     */ 
    public function getModel()
    {
        return $this->model;
    }
}