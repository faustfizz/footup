<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Database
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Database;

use Exception;
use Footup\Database\DbConnection;
use PDO;
use PDOException;
use PDOStatement;

class QueryBuilder implements \IteratorAggregate
{
    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $returnType = PDO::FETCH_OBJ;

    /**
     * @var string $selectFields
     */
    protected $selectFields;

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
     * @var \PDO $db connection
     */
    protected static $db = null;

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
     * @param string $table the table that we work on
     * @param PDO $DbConnection
     */
    public function __construct(string $table, $DbConnection = null)
    {
        $this->from($table);
        self::$db = $DbConnection instanceof PDO ? $DbConnection : DbConnection::setDb(null, true);
        $this->getPrimaryKey();
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
            $this->reset();
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
    public function join($table, $fields, $type = ' INNER ', $operator = " = ")
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
    public function leftJoin($table, $fields, $operator = " = ")
    {
        return $this->join($table, $fields, ' LEFT OUTER ', $operator);
    }

    /**
     * Adds a right table join.
     *
     * @param string $table Table to join to
     * @param array|string $fields Fields to join on
     * @return QueryBuilder Self reference
     */
    public function rightJoin($table, $fields, $operator = " = ")
    {
        return $this->join($table, $fields, ' RIGHT OUTER ', $operator);
    }

    /**
     * Adds a full table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return QueryBuilder Self reference
     */
    public function fullJoin($table, $fields, $operator = " = ")
    {
        return $this->join($table, $fields, ' FULL OUTER ', $operator);
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
        $this->where .= (empty($this->where)) ? ' WHERE ' : '';

        if (is_array($key)) {
            $key = array_filter($key);
            $counter = count($key);
            foreach ($key as $k => $v) {
                $glue = !empty($this->where) && trim(strtolower($this->where)) != 'where' ? $link : '';
                $this->where .= $counter > 1 ? ' (' : '';
                $counter--;
                $this->where .= $glue . $k . ' ' . trim($operator ?? " = ") . ' ' . ($escape && !is_numeric($v) ? $this->quote($v) : $v);
                $this->where .= $counter == 0 && count($key) > 1 ? ') ' : '';
            }
        } else if (is_string($key) && is_null($val)) {
            $link = !empty($this->where) && trim(strtolower($this->where)) != 'where'  ? $link : '';
            $this->where .= $link . $key;
        } else {
            $link = !empty($this->where) && trim(strtolower($this->where)) != 'where'  ? $link : '';
            if (trim(strtolower($operator)) != 'is') {
                if (is_null($val)) {
                    $operator = "IS NOT NULL";
                }
                if (is_string($val) && !empty($val)) {
                    $val = ($escape && !is_numeric($val) ? $this->quote($val) : $val);
                }
                if (is_array($val) && !empty($val)) {
                    $val = '(' . implode(',', array_map(array($this, 'quote'), $val)) . ')';
                    $operator = ' IN ';
                }
            }
            $this->where .= trim($link . $key . ' ' . trim($operator ?? " = ") . ' ' . $val);
        }

        return $this;
    }

    /**
     * @param string|array $key
     * @param array|string $val
     * @param string $operator
     * @return QueryBuilder
     */
    public function orWhere($key, $val = null, $operator = null, $escape = true)
    {
        return $this->where($key, $val, $operator, ' OR ', $escape);
    }

    /**
     * @param string $key
     * @param array|string $val
     * @return QueryBuilder
     */
    public function whereIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' IN ', ' AND ', $escape);
    }

    /**
     * @param $key
     * @param array $val
     * @return QueryBuilder
     */
    public function whereNotIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' NOT IN ', ' AND ', $escape);
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
    public function orWhereIn(array|string $key, array $val, $escape = true)
    {
        return $this->orWhere($key, $val, ' IN ', $escape);
    }

    /**
     * @param array|string $key
     * @param array $val
     * @return QueryBuilder
     */
    public function orWhereNotIn(array|string $key, array $val, $escape = true)
    {
        return $this->orWhere($key, $val, ' NOT IN ', $escape);
    }

    /**
     * @param $str
     * @param array|null $build_data
     * @param string $link
     * @return QueryBuilder
     */
    public function orWhereRaw($str)
    {
        return $this->orWhere($str);
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function orWhereNotNull($key)
    {
        return $this->orWhere($key, ' NOT NULL ', ' IS ');
    }

    /**
     * @param $key
     * @return QueryBuilder
     */
    public function orWhereNull($key)
    {
        return $this->orWhere($key, 'NULL', ' IS ');
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string|array $field Field name
     * @return QueryBuilder Self reference
     */
    public function asc($field = null)
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string|array $field Field name
     * @return QueryBuilder Self reference
     */
    public function desc($field = null)
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * Adds fields to order by.
     *
     * @param string|array $field Field name
     * @param string $direction Sort direction
     * @return QueryBuilder Self reference
     */
    public function orderBy(mixed $field, $direction = 'ASC')
    {
        $this->order = (empty($this->order)) ? 'ORDER BY ' : $this->order.',';

        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $field[$key] = $value . ' ' . $direction;
            }
        } else {
            $field = ($field ?? $this->getPrimaryKey()) .' ' . $direction;
        }

        $fields = (is_array($field)) ? implode(', ', $field) : $field;

        $this->order .= $fields;

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
        $join = (empty($this->groups)) ? 'GROUP BY ' : ',';
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
        $join = (empty($this->having)) ? 'HAVING ' : '';
        if(is_array($field))
        {   $thisModel = $this;
            $fields = array_map(function($key, $value) use ($thisModel){
                $chain = is_numeric($key) ? $thisModel->primaryKey." = ".$thisModel->quote($value) : "$key = ".$thisModel->quote($value);
                return $chain;
            }, array_keys($field), array_values($field));
            $join .= implode(',', $fields);
        }else{
            $join .= !empty($value) ? $field." = ".$this->quote($value) : $field;
        }
        $this->having .= $join;

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
        if ($limit !== null && empty($this->limit)) {
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
        if ($offset !== null && empty($this->offset)) {
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
        $this->distinct = ($value) ? 'DISTINCT ' : '';

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

        if($fields === "*" && !empty($this->selectFields)) return $this;

        $this->selectFields .= !empty($this->selectFields) ?  ", ".$fields : $fields;

        return $this;
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values or array of keys to insert
     * @param array $values Array of values to use prepared statement
     * @return bool|int
     */
    public function insert(array $data = [], $values = [])
    {
        $this->checkTable();

        if (empty($data)) return false;

        if(!empty($values))
        {
            $keys = implode(',', array_values($data));
            $vals = str_repeat("? , ", count($values));
        }else{
            $keys = implode(',', array_keys($data));
            $vals = implode(',', array_values(
                array_map(
                    array($this, 'quote'),
                    $data
                )
            ));
        }
        
        $this->sql(array(
            'INSERT INTO',
            ($this->getTable()),
            '(' . $keys . ')',
            'VALUES',
            '(' . trim($vals, " ,") . ')'
        ));

        $insert = $this->execute($values);

        return $insert->ok;
    }

    /**
     * Builds an update query.
     *
     * @param array|\stdClass $data Array of keys and values or object of type \stdClass
     * @param int|null $id 
     * @return bool 
     */
    public function update($data, $id = null)
    {
        $this->checkTable();
        $data = is_object($data) ? get_object_vars($data) : $data;

        $values = array();
        
        $id && $this->where($this->getPrimaryKey() . " = " . $id);
        
        if (empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["UPDATE"]));
        }
        
        foreach ($data as $key => $value) {
            if(is_numeric($key))
            {
                throw new Exception("Data array should be in format [field => value] !");
            }
            $values[] = $key . " = " . $this->quote($value);
        }
        
        $this->sql(array(
            'UPDATE',
            $this->getTable(),
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

        $where && $this->where(is_int($where) ? $this->getPrimaryKey()." = ".$where : $where);

        if (empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["DELETE"]));
        }

        $this->sql(array(
            'DELETE FROM',
            $this->getTable(),
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
                strtr(
                    is_array($sql) ? array_reduce($sql, array($this, 'build')) : $sql,
                    ["  " => " "]
                )
            );

            return $this;
        }

        return $this->sql;
    }

    /*** Database Access Methods ***/

    /**
     * Executes a sql statement.
     *
     * @throws Exception When database is not defined
     * @param array $params
     * @return object Query results object
     */
    public function execute(array $params = [])
    {
        if (!self::$db) {
            throw new Exception(text("Db.undefinedDB"));
        }

        $result = null;

        $this->setNumRows(0)
            ->setAffectedRows(0)
            ->setInsertId(-1)
            ->setLastQuery($this->sql);

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

                    $this->setNumRows($result->rowCount())
                        ->setAffectedRows($result->rowCount())
                        ->setInsertId(-1)
                        ->setInsertId(self::$db->lastInsertId());
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
        
        $res = ['ok' => ($bool && $this->getInsertID() ? $this->getInsertID() : $bool), 'result' => $result];
        return (object)$res;
    }

    /**
     * Undocumented function
     *
     * @param string $select
     * @param array|string $where
     * @param int $limit
     * @param int $offset
     * @return \stdClass[]
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

        return $result->fetchAll($this->getReturnType());
    }

    /**
     * Fetch a single row from a select query.
     * 
     * @param string $fields
     * @param string|array $where
     *
     * @return \stdClass|\Footup\Orm\BaseModel|null Row
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
     * @return \stdClass|\Footup\Orm\BaseModel|null Row
     */
    public function first($field = null, $where = null)
    {
        if (empty($this->sql)) {
            $this->asc($field);
        }
        return $this->one(null, $where);
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $field
     * @param string|array $where
     *
     * @return \stdClass|\Footup\Orm\BaseModel|null Row
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
     * Get the table name for this ER class.
     * 
     * @access public
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        if(empty($this->primaryKey))
        {
            $columns = $this->getTableInfo();
            
            foreach ($columns as $key => $column) {
                # code...
                if($column["Key"] === "PRI")
                {
                    $this->primaryKey = $column["Field"];
                    break;
                }
            }
        }
        
        return $this->primaryKey;
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getReturnType()
    {
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
     * Undocumented function
     *
     * @todo i need help here 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        # code...
        $data = $this->get();
        return new \ArrayIterator($data ?? []);
    }

    /**
     * Set the value of returnType
     *
     * @param  string  $returnType
     *
     * @return  self
     */ 
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * Set the value of primaryKey
     *
     * @param  string  $primaryKey
     *
     * @return  self
     */ 
    public function setPrimaryKey(string $primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Set $insert_id id dernièrement ajouté
     *
     * @param  int|string  $insert_id  $insert_id id dernièrement ajouté
     *
     * @return  self
     */ 
    public function setInsertId($insert_id)
    {
        $this->insert_id = $insert_id;

        return $this;
    }

    /**
     * Set $num_rows
     *
     * @param  int  $num_rows  $num_rows
     *
     * @return  self
     */ 
    public function setNumRows(int $num_rows)
    {
        $this->num_rows = $num_rows;

        return $this;
    }

    /**
     * Set $affcted_rows
     *
     * @param  int  $affected_rows  $affcted_rows
     *
     * @return  self
     */ 
    public function setAffectedRows(int $affected_rows)
    {
        $this->affected_rows = $affected_rows;

        return $this;
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