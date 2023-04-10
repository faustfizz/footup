<?php

/**
 * FOOTUP - FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.1
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
        if($this->model->{$this->getPrimaryKey()} && empty($where))
        {
            return parent::delete($this->getPrimaryKey()." = ".$this->model->{$this->getPrimaryKey()});
        }
        return parent::delete($where);
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
                default:
                return $result->fetchAll(PDO::FETCH_CLASS, get_class($this->model));
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

        $data = $object->getAttributes();

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
                    $id,
                    array_filter($data, 
                        function($v, $k) {
                            return trim($v) !== "";
                        }, ARRAY_FILTER_USE_BOTH
                    )
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
     * @return array|string|false $tableInfo
     */
    public function getTableInfo()
    {
        if (empty($this->tableInfo)) {
            $stmt = self::$db->prepare(
                "SHOW COLUMNS FROM " . ($this->getTable()) . ";"
            );
            $stmt->execute();
            $this->tableInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->tableInfo;
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