<?php

/**
 * FOOTUP - FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.2
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
        self::$db = $DbConnection instanceof PDO ? $DbConnection : DbConnection::getDb();

        $this->getTable();
        $this->getPrimaryKey();
        $this->getReturnType();

        parent::__construct($this->getTable(), self::$db);
    }

    /*** Core Methods ***/

    /**
     * Builds a delete query.
     *
     * @param string|array|int $where Where conditions
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
     * Undocumented function
     *
     * @param string $select
     * @param array|string $where
     * @param int $limit
     * @param int $offset
     * @return BaseModel[]
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

        switch ($this->returnType) {
            case 'object':
                return $result->fetchAll(PDO::FETCH_OBJ);
            
            case 'array':
                return $result->fetchAll(PDO::FETCH_ASSOC);
                
            case 'self':
                default:{
                    $items = $result->fetchAll(PDO::FETCH_ASSOC);
                    $model = get_class($this->model);
                    return array_map(function ($item) use ($model) {
                        $Model = new $model;
                        $Model->fill($item);
                        return $Model->setOriginalData($Model->getData());
                    }, $items);
                }
        }

    }

    /**
     * Saves an object to the database.
     *
     * @param \Footup\Orm\BaseModel $object Class instance
     * @param array $fields Select database fields to save
     * @return bool
     */
    public function save(BaseModel $object = null, array $fields = null)
    {
        $object = is_null($object) ? $this->model : $object;

        $this->from($object->getTable());

        $pk = $object->getPrimaryKey();
        $id = $object->{$pk} ?? null;
        
        $data = $object->getData();

        if (is_null($id)) {
            if ($bool = $this->insert(
                    array_filter($data, 
                        function($v, $k) {
                            return  trim($v) !== "";
                        }, ARRAY_FILTER_USE_BOTH
                    )
                )
            ) {
                $object->{$pk} = $this->getInsertID();
            }
            return $bool;
        } else {
            if ($fields !== null) {
                $keys = array_flip($fields);
                $data = array_intersect_key($data, $keys);
            }
            
            return $this->update(
                    array_filter($data, 
                        function($v, $k) {
                            return trim($v) !== "";
                        }, ARRAY_FILTER_USE_BOTH
                    ),
                    $id
                );
        }
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

    /**
     * Get $db connection
     *
     * @return  \PDO
     */ 
    public function getDb()
    {
        return self::$db;
    }
}