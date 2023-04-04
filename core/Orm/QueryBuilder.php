<?php

/**
 * FOOTUP - 0.1.6-Alpha - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use Exception;
use Footup\Database\DbConnection;
use PDO;
use PDOException;
use PDOStatement;

class QueryBuilder
{
    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $returnType;

    /**
     * @var string $where
     */
    protected $where;

    /**
     * @var string $joins
     */
    protected $joins;

    /**
     * @var string $order
     */
    protected $order;

    /**
     * @var string $groups
     */
    protected $groups;

    /**
     * @var string $having
     */
    protected $having;

    /**
     * @var string $distinct
     */
    protected $distinct;

    /**
     * @var string $limit
     */
    protected $limit;

    /**
     * @var string $offset
     */
    protected $offset;

    /**
     * @var string $sql SQL statement
     */
    protected $sql;

    /**
     * @var int
     */
    protected $page_count = null;
    
    /**
     * @var int
     */
    protected $current_page = 0;
    
    /**
     * @var int
     */
    protected $per_page = 10;

    /**
     * @var \PDO $db connection
     */
    protected static $db = null;

    /**
     * @var string|object $class classe courrante
     */
    protected $class;

    /**
     * @var string $last_query dernière requête sql
     */
    public $last_query;

    /**
     * @var int $num_rows
     */
    public $num_rows;

    /**
     * @var int|string $insert_id id dernièrement ajouté
     */
    public $insert_id;

    /**
     * @var int $affcted_rows
     */
    public $affected_rows;

    /**
     * @var bool $show_sql voir la requête dans l'erreur
     */
    public $show_sql = true;

    /**
     * @var array|string $tableInfo informations de la table
     */
    protected $tableInfo  = [];

    /**
     * QueryBuilder constructor
     *
     * @param BaseModel $model the model that use the query builder
     * @param DbConnection $DbConnection
     */
    public function __construct(BaseModel $model, PDO $DbConnection = null)
    {
        $this->class = $model;
        self::$db = !is_null($DbConnection) ? $DbConnection : DbConnection::getDb();

        $this->getTable();
        $this->getPrimaryKey();
        $this->getReturnType();
    }

    /*** Core Methods ***/

    /**
     * @param string $sql SQL statement
     * @param string $input Input string to append
     * @return string New SQL statement
     */
    private function build($sql, $input)
    {
        return (strlen($input) > 0) ? ($sql . ' ' . $input) : $sql;
    }

    /**
     * Checks whether the table property has been set.
     * @throws Exception
     */
    private function checkTable()
    {
        if (!$this->getTable()) {
            throw new Exception(text("Db.undefinedTable"));
        }
    }

    /**
     * Checks whether the class property has been set.
     * @throws Exception
     */
    private function checkClass()
    {
        if (!$this->class) {
            throw new Exception(text("Db.modelClassUndefined"));
        }
    }

    /**
     * Resets class properties.
     * 
     * @return QueryBuilder
     */
    public function reset()
    {
        $this->where    =
            $this->joins    =
            $this->order    =
            $this->groups   =
            $this->having   =
            $this->distinct =
            $this->limit    =
            $this->offset   =
            $this->sql      = '';

        return $this;
    }

    /*** SQL Builder Methods ***/

    /**
     * Sets the table.
     *
     * @param string $table Table name
     * @param boolean $reset Reset class properties
     * @return QueryBuilder Self reference
     */
    public function from($table, $reset = true)
    {
        $this->table = $table;
        if ($reset) {
            return $this->reset();
        }
        return $this;
    }

    /**
     * Adds a table join.
     *
     * @param string $table Table to join to
     * @param array|string $fields Fields to join on
     * @param string $type Type of join
     * @return QueryBuilder Self reference
     * @throws Exception For invalid join type
     */
    public function join($table, $fields, $type = 'INNER', $operator = '=')
    {
        static $joins = array(
            'INNER',
            'LEFT OUTER',
            'RIGHT OUTER',
            'FULL OUTER',
            'LEFT',
            'RIGHT',
            'FULL'
        );

        if (!in_array($type, $joins)) {
            throw new Exception(text("Db.invalidJoin", [$type]));
        }

        $this->joins .= ' ' . $type . ' JOIN ' . $table . ' ON ' .
            (is_string($fields) ? $fields : '');

        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                $this->joins .= $key . ' ' . $operator . ' ' . $value;
            }
        }

        return $this;
    }

    /**
     * Adds a left table join.
     *
     * @param string $table Table to join to
     * @param array|string $fields Fields to join on
     * @return QueryBuilder Self reference
     */
    public function leftJoin($table, $fields, $operator = '=')
    {
        return $this->join($table, $fields, 'LEFT OUTER', $operator);
    }

    /**
     * Adds a right table join.
     *
     * @param string $table Table to join to
     * @param array|string $fields Fields to join on
     * @return QueryBuilder Self reference
     */
    public function rightJoin($table, $fields, $operator = '=')
    {
        return $this->join($table, $fields, 'RIGHT OUTER', $operator);
    }

    /**
     * Adds a full table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return QueryBuilder Self reference
     */
    public function fullJoin($table, $fields, $operator = '=')
    {
        return $this->join($table, $fields, 'FULL OUTER', $operator);
    }

    /**
     * @param array|string $key
     * @param string|array $val
     * @param string $operator
     * @param string $link
     * @return QueryBuilder
     */
    public function where($key, $val = null, $operator = null, $link = ' AND ', $escape = true)
    {
        $this->where .= (empty($this->where)) ? 'WHERE ' : '';

        if (is_array($key)) {
            $key = array_filter($key);
            $counter = count($key);
            foreach ($key as $k => $v) {
                $glue = !empty($this->where) && !in_array(trim($this->where), ['WHERE', 'where']) ? $link : '';
                $this->where .= $counter > 1 ? ' (' : '';
                $counter--;
                $this->where .= $glue . $k . ' ' . trim($operator ?? '=') . ' ' . ($escape && !is_numeric($v) ? $this->quote($v) : $v);
                $this->where .= $counter == 0 && count($key) > 1 ? ') ' : '';
            }
        } else if (is_string($key) && is_null($val)) {
            $link = !empty($this->where) && !in_array(trim($this->where), ['WHERE', 'where'])  ? $link : '';
            $this->where .= $link . $key;
        } else {
            $link = !empty($this->where) && !in_array(trim($this->where), ['WHERE', 'where'])  ? $link : '';
            if (trim($operator) != 'IS' && trim($operator) != 'is') {
                if (is_array($val) && !empty($val)) {
                    $val = '(' . implode(',', array_map(array($this, 'quote'), $val)) . ')';
                    $operator = ' IN ';
                }
                if (is_null($val)) {
                    $operator = "IS NOT NULL";
                }
                if (is_string($val) && !empty($val)) {
                    $val = ($escape && !is_numeric($val) ? $this->quote($val) : $val);
                }
            }
            $this->where .= trim($link . $key . ' ' . trim($operator ?? '=') . ' ' . $val);
        }

        return $this;
    }

    /**
     * @param string|array $key
     * @param array|string $val
     * @param string $operator
     * @return QueryBuilder
     */
    public function whereOr(array|string $key, $val = null, $operator = null, $escape = true)
    {
        return $this->where($key, $val, $operator, ' OR ', $escape);
    }

    /**
     * @param array|string $key
     * @param array|string $val
     * @return QueryBuilder
     */
    public function whereIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' IN ', 'AND', $escape);
    }

    /**
     * @param $key
     * @param array $val
     * @return QueryBuilder
     */
    public function whereNotIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' NOT IN ', 'AND', $escape);
    }

    /**
     * @param $str
     * @param array|null $build_data
     * @param string $link
     * @return QueryBuilder
     */
    public function whereRaw($str)
    {
        return $this->where($str);
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function whereNotNull($key)
    {
        return $this->where($key, 'NOT NULL', ' IS ');
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function whereNull($key)
    {
        return $this->where($key, 'NULL', ' IS ');
    }

    // where OR

    /**
     * @param array|string $key
     * @param array|string $val
     * @return QueryBuilder
     */
    public function whereOrIn(array|string $key, array $val, $escape = true)
    {
        return $this->whereOr($key, $val, ' IN ', $escape);
    }

    /**
     * @param array|string $key
     * @param array $val
     * @return QueryBuilder
     */
    public function whereOrNotIn(array|string $key, array $val, $escape = true)
    {
        return $this->whereOr($key, $val, ' NOT IN ', $escape);
    }

    /**
     * @param $str
     * @param array|null $build_data
     * @param string $link
     * @return QueryBuilder
     */
    public function whereOrRaw($str)
    {
        return $this->whereOr($str);
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function whereOrNotNull($key)
    {
        return $this->whereOr($key, ' NOT NULL ', ' IS ');
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function whereOrNull($key)
    {
        return $this->whereOr($key, 'NULL', ' IS ');
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string $field Field name
     * @return QueryBuilder Self reference
     */
    public function asc($field)
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string $field Field name
     * @return QueryBuilder Self reference
     */
    public function desc($field)
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * Adds fields to order by.
     *
     * @param string $field Field name
     * @param string $direction Sort direction
     * @return QueryBuilder Self reference
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $join = (empty($this->order)) ? 'ORDER BY' : ',';

        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $field[$key] = $value . ' ' . $direction;
            }
        } else {
            $field .= ' ' . $direction;
        }

        $fields = (is_array($field)) ? implode(', ', $field) : $field;

        $this->order .= $join . ' ' . $fields;

        return $this;
    }

    /**
     * Adds fields to group by.
     *
     * @param string|array $field Field name or array of field names
     * @return QueryBuilder Self reference
     */
    public function groupBy($field)
    {
        $join = (empty($this->order)) ? 'GROUP BY' : ',';
        $fields = (is_array($field)) ? implode(',', $field) : $field;

        $this->groups .= $join . ' ' . $fields;

        return $this;
    }

    /**
     * Adds having conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return QueryBuilder Self reference
     */
    public function having($field, $value = null)
    {
        $join = (empty($this->having)) ? 'HAVING' : '';
        $this->having .= $this->where($field, $value, $join);

        return $this;
    }

    /**
     * Adds a limit to the query.
     *
     * @param int $limit Number of rows to limit
     * @param int $offset Number of rows to offset
     * @return QueryBuilder Self reference
     */
    public function limit($limit = null, $offset = null)
    {
        if ($limit !== null) {
            $this->limit = 'LIMIT ' . $limit;
        }
        if ($offset !== null) {
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * Adds an offset to the query.
     *
     * @param int $offset Number of rows to offset
     * @param int $limit Number of rows to limit
     * @return QueryBuilder Self reference
     */
    public function offset($offset, $limit = null)
    {
        if ($offset !== null) {
            $this->offset = 'OFFSET ' . $offset;
        }
        if ($limit !== null) {
            $this->limit($limit);
        }

        return $this;
    }

    /**
     * Sets the distinct keyword for a query.
     * 
     * @param bool $value
     * @return QueryBuilder
     */
    public function distinct($value = true)
    {
        $this->distinct = ($value) ? 'DISTINCT' : '';

        return $this;
    }

    /**
     * Sets a between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     * @return QueryBuilder
     */
    public function between($field, $value1, $value2)
    {
        $this->where(sprintf(
            '%s BETWEEN %s AND %s',
            $field,
            $this->quote($value1),
            $this->quote($value2)
        ));
        return $this;
    }

    /**
     * Builds a select query.
     *
     * @param array|string $fields Array of field names to select
     * @param int $limit Limit condition
     * @param int $offset Offset condition
     * @return QueryBuilder Self reference
     */
    public function select($fields = '*', $limit = null, $offset = null)
    {
        $this->checkTable();

        $fields = (is_array($fields)) ? implode(',', $fields) : $fields;
        $this->limit($limit, $offset);

        return $this->sql(array(
            'SELECT',
            $this->distinct,
            $fields,
            'FROM',
            $this->getTable(),
            $this->joins,
            $this->where,
            $this->groups,
            $this->having,
            $this->order,
            $this->limit,
            $this->offset
        ));
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @return bool|int
     */
    public function insert(array $data = [])
    {
        $this->checkTable();

        if (empty($data)) return false;

        $keys = implode(',', array_keys($data));
        $values = implode(',', array_values(
            array_map(
                array($this, 'quote'),
                $data
            )
        ));
        
        $this->sql(array(
            'INSERT INTO',
            $this->getTable(),
            '(' . $keys . ')',
            'VALUES',
            '(' . $values . ')'
        ));

        $insert = $this->execute();

        return $insert->ok;
    }

    /**
     * Builds an update query.
     *
     * @param array $data Array of keys and values, or string literal
     * @return bool 
     */
    public function update($data)
    {
        $this->checkTable();

        $values = array();

        $pk = $this->getPrimaryKey();
        $id = $this->class->{$pk} ?? $this->insert_id ?? null;

        if(!isset($data[$pk]) && !is_null($id))
        {
            $this->where($pk . " = " . $id);
        }else{
            $id = isset($data[$pk]) ? $data[$pk] : $id;
            $this->where($pk . " = " . $id);
        }
        
        if (empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["UPDATE"]));
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $values[] = is_numeric($key) ? $value : $key . '=' . $this->quote($value);
            }
        } else {
            $values[] = (string)$data;
        }
        
        $this->sql(array(
            'UPDATE',
            $this->table,
            'SET',
            implode(',', $values),
            $this->where
        ));

        $ex = $this->execute();
        $execute = $ex->ok;

        return $execute;
    }

    /**
     * Builds a delete query.
     *
     * @param string|array|int $where Where conditions
     * @return bool
     */
    public function delete($where = null)
    {
        $this->checkTable();

        $pk = $this->getPrimaryKey();
        $id = is_int($where) ? $where : $this->class->{$pk} ?? $this->insert_id ?? null;

        if (is_string($where) || is_array($where)) {
            $this->where($where);
        }else{
            $this->where($pk . " = " . $id);
        }

        if (empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["DELETE"]));
        }

        $this->sql(array(
            'DELETE FROM',
            $this->table,
            $this->where
        ));

        $execute = ($this->execute())->ok;

        return $execute;
    }

    /**
     * Gets or sets the SQL statement.
     *
     * @param string|array SQL statement
     * @return QueryBuilder|string SQL statement
     */
    public function sql($sql = null)
    {
        if ($sql !== null) {
            $this->sql = trim(
                (is_array($sql)) ?
                    array_reduce($sql, array($this, 'build')) :
                    $sql
            );

            return $this;
        }

        return $this->sql;
    }

    /*** Database Access Methods ***/

    /**
     * Sets the database connection.
     *
     * @param \PDO|DbConnection|string|array $config
     * @param boolean $init
     * @throws Exception For connection error
     * @return QueryBuilder
     */
    public function setDb($config = null, $init = true)
    {
        self::$db = DbConnection::setDb($config, $init);
        return $this;
    }

    /**
     * Gets the database connection.
     *
     * @return object Database connection
     */
    public function getDb()
    {
        return self::$db;
    }

    /**
     * Executes a sql statement.
     *
     * @throws Exception When database is not defined
     * @param array $params
     * @return object Query results object
     */
    public function execute(array $params = [])
    {
        if (!self::$db && !$this->setDb()) {
            throw new Exception(text("Db.undefinedDB"));
        }

        $result = null;

        $this->num_rows = 0;
        $this->affected_rows = 0;
        $this->insert_id = -1;
        $this->setLastQuery($this->sql);

        if (!empty($this->sql))
        {
            $error = null;

            try {
                $result = self::$db->prepare($this->sql);

                if (!$result) {
                    $error = self::$db->errorInfo()[2];
                } else {
                    $bool = $result->execute($params);
                    $error = !$bool ? $result->errorInfo()[2] : null;

                    $this->num_rows = $result->rowCount();
                    $this->affected_rows = $result->rowCount();
                    $this->insert_id = self::$db->lastInsertId();
                }
            } catch (PDOException $ex) {
                $error = $ex->getMessage();
            }

            if ($error !== null) {
                if ($this->show_sql) {
                    $error .= "\nSQL: " . $this->sql;
                }
                throw new Exception('Database error: ' . $error);
            }
        }

        $this->reset();
        
        $res = ['ok' => ($bool && $this->insert_id ? $this->insert_id : $bool), 'result' => $result];
        return (object)$res;
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
        if (!is_null($where)) {
            $this->where($where);
        }
        if (empty($this->sql)) {
            $this->select($select, $limit, $offset);
        }

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
                return $result->fetchAll(PDO::FETCH_CLASS, get_class($this->class));
        }

    }

    /**
     * Fetch a single row from a select query.
     * 
     * @param string $fields
     * @param string|array $where
     *
     * @return BaseModel|null Row
     */
    public function one($fields = null, $where = null)
    {
        if (empty($this->sql)) {
            $this->limit(1)->select();
        }

        $data = $this->get($fields ?? "*");

        $row = (!empty($data)) ? $data[0] : null;

        return $row;
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $field
     * @param string|array $where
     *
     * @return BaseModel|null Row
     */
    public function first($field = null, $where = null)
    {
        return $this->asc($field ?? $this->getPrimaryKey())->one(null, $where);
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $field
     * @param string|array $where
     *
     * @return BaseModel|null Row
     */
    public function last($field = null, $where = null)
    {
        if (empty($this->sql)) {
            $this->desc($field ?? $this->getPrimaryKey());
        }

        return $this->one(null, $where);
    }

    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * @return mixed Row value
     */
    public function value($name)
    {
        $row = $this->one();

        $value = (!empty($row)) ? $row->$name : null;

        return $value;
    }

    /**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * @return mixed Row value
     */
    public function min($field, $key = null)
    {
        $this->select('MIN(' . $field . ') min_value');

        return $this->value(
            'min_value'
        );
    }

    /**
     * Gets the max value for a specified field.
     *
     * @param string $field Field name
     * @return mixed Row value
     */
    public function max($field, $key = null)
    {
        $this->select('MAX(' . $field . ') max_value');

        return $this->value(
            'max_value'
        );
    }

    /**
     * Gets the sum value for a specified field.
     *
     * @param string $field Field name
     * @return mixed Row value
     */
    public function sum($field, $key = null)
    {
        $this->select('SUM(' . $field . ') sum_value');

        return $this->value(
            'sum_value'
        );
    }

    /**
     * Gets the average value for a specified field.
     *
     * @param string $field Field name
     * @return mixed Row value
     */
    public function avg($field, $key = null)
    {
        $this->select('AVG(' . $field . ') avg_value');

        return $this->value(
            'avg_value'
        );
    }


    /**
     * Gets a count of records for a table.
     *
     * @param string $field Field name
     * @return int Row value
     */
    public function count($field = '*')
    {
        $this->select('COUNT(' . $field . ') num_rows');

        return $this->value(
            'num_rows'
        );
    }

    /**
     * Wraps quotes around a string and escapes the content for a string parameter.
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     */
    public function quote($value)
    {
        if ($value === null) return 'NULL';

        if (is_string($value)) {
            if (self::$db !== null) {
                return self::$db->quote($value);
            }

            $value = str_replace(
                array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
                $value
            );

            return "'$value'";
        }

        return $value;
    }

    /**
     * Finds and populates an object.
     *
     * @param int|string|array Search value
     * @param string $field Search value
     * @return object|array|null Populated object
     */
    public function find($value = [], $field = null)
    {
        $field = is_null($field) ? $this->getPrimaryKey() : $field;

        $this->from($this->table ?? $this->getTable(), false);

        if (!empty($value)) {
            if ((is_int($value) || is_string($value)) && ($this->getPrimaryKey() == $field || property_exists($this, $field))) {
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
     * Saves an object to the database.
     *
     * @param \Footup\Orm\BaseModel $object Class instance
     * @param array $fields Select database fields to save
     * @return bool
     */
    public function save(BaseModel $object = null, array $fields = null)
    {
        $object = is_null($object) ? $this->class : $object;

        $this->from($object->getTable());

        $pk = $object->getPrimaryKey();
        $id = $object->class->{$pk} ?? null;

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
                $object->class->{$pk} = $this->insert_id;
            }
            return $bool;
        } else {
            if ($fields !== null) {
                $keys = array_flip($fields);
                $data = array_intersect_key($data, $keys);
            }
            
            return $this->where($pk, $id)
                ->update(
                    array_filter($data, 
                        function($v, $k) {
                            return trim($v) !== "";
                        }, ARRAY_FILTER_USE_BOTH
                    )
                );
        }
    }

    /**
     * Removes an object from the database.
     *
     * @param \Footup\Orm\BaseModel $object Class instance
     * @return bool
     */
    public function remove($object = null)
    {
        $object = is_null($object) ? $this->class : $object;

        $this->from($object->getTable());

        $pk = $object->getPrimaryKey();
        $id = $object->class->{$pk} ?? null;

        if ($id !== null) {
            return $this->where($pk, $id)
                ->delete();
        }
        return false;
    }

    /**
     * Get the table name for this ER class.
     * 
     * @access public
     * @return string
     */
    public function getTable()
    {
        return $this->table = $this->class->getTable();
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey = $this->class->getPrimaryKey();
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType = $this->class->getReturnType();
    }

    /**
     * @return array|string|false $tableInfo
     */
    public function getTableInfo()
    {
        if (empty($this->tableInfo)) {
            $stmt = self::$db->prepare(
                "SHOW COLUMNS FROM " . $this->getTable() . ";"
            );
            $stmt->execute();
            $this->tableInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->tableInfo;
    }

    /**
     * Create new data row.
     *
     * @param array $properties
     * 
     * @return bool
     */
    public function create(array $properties)
    {
        return $this->insert($properties);
    }


    /**
     * Find one model in the database.
     * or create if not exists.
     *
     * @param array $properties
     * 
     * @return BaseModel[]|bool|array
     */
    public function findOrCreate(array $properties = null)
    {
        // search for model and create if not exists
        $object = $this->get('*', $properties);
        if (empty($object) && !empty($properties)) {
            return $this->create($properties);
        } else {
            return $object;
        }
    }


    /**
     * Get $last_query dernière requête sql
     *
     * @return  string
     */ 
    public function getLastQuery()
    {
        return $this->last_query;
    }

    /**
     * Set $last_query dernière requête sql
     *
     * @param  string  $last_query  $last_query dernière requête sql
     *
     * @return  self
     */ 
    public function setLastQuery(string $last_query)
    {
        $this->last_query = $last_query;

        return $this;
    }

    /**
     * Get $num_rows
     *
     * @return  int
     */ 
    public function getNumRows()
    {
        return $this->num_rows;
    }

    /**
     * Get $insert_id id dernièrement ajouté
     *
     * @return  int|string
     */ 
    public function getInsertID()
    {
        return $this->insert_id;
    }

    /**
     * Get $affcted_rows
     *
     * @return  int
     */ 
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * Get $class classe courrante
     *
     * @return  string|object
     */ 
    public function getModel()
    {
        return $this->class;
    }
}