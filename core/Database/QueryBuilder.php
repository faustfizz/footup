<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Database
 * @version 0.4
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
     * Supported operators
     */
    const OPERATORS = ['=', '!=', '<>', '>', '>=', '<', '<=', 'IS', 'IS NOT', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN'];

    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * A supported PDO FETCH type
     * @var int
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
    protected $tableInfo = [];

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
        $pk = $this->getPrimaryKey();
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
        $this->where =
            $this->selectFields =
            $this->joins =
            $this->order =
            $this->groups =
            $this->having =
            $this->distinct =
            $this->limit =
            $this->offset =
            $this->sql = '';

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
     * @param string $operator default = 
     * 
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
     * @param string $operator default =
     * 
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
     * @param string $operator default =
     * 
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
     * @param string $operator default =
     * 
     * @return QueryBuilder Self reference
     */
    public function fullJoin($table, $fields, $operator = " = ")
    {
        return $this->join($table, $fields, ' FULL OUTER ', $operator);
    }

    /**
     * @param array|string $key
     * @param array|string|null $operatorOrValue
     * @param array|string|null $val
     * @param string $link default AND 
     * @param bool $escape default TRUE
     * 
     * @return QueryBuilder
     */
    public function where($key, $operatorOrValue = null, $val = null, $link = ' AND ', $escape = true)
    {
        return $this->buildWhere($key, $operatorOrValue, $val, $link, $escape);
    }

    /**
     * @param string|array $key
     * @param array|string|null $operatorOrValue
     * @param array|string|null $val
     * @param bool $escape default TRUE
     * 
     * @return QueryBuilder
     */
    public function orWhere($key, $operatorOrValue = null, $val = null, $escape = true)
    {
        return $this->where($key, $operatorOrValue, $val, ' OR ', $escape);
    }

    /**
     * @param string $key
     * @param array $val
     * @param bool $escape default TRUE
     * 
     * @return QueryBuilder
     */
    public function whereIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' IN ', ' AND ', $escape);
    }

    /**
     * @param string $key
     * @param array $val
     * @param bool $escape default TRUE
     * 
     * @return QueryBuilder
     */
    public function whereNotIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' NOT IN ', ' AND ', $escape);
    }

    /**
     * @param mixed $str
     * 
     * @return QueryBuilder
     */
    public function whereRaw($str)
    {
        return $this->where($str);
    }

    /**
     * @param string $key
     * 
     * @return QueryBuilder
     */
    public function whereNotNull($key)
    {
        return $this->where($key, ' IS NOT ', 'NULL');
    }

    /**
     * @param string $key
     * 
     * @return QueryBuilder
     */
    public function whereNull($key)
    {
        return $this->where($key, ' IS ', 'NULL');
    }

    // where OR

    /**
     * @param string $key
     * @param array $val
     * 
     * @return QueryBuilder
     */
    public function orWhereIn(string $key, array $val, $escape = true)
    {
        return $this->orWhere($key, ' IN ', $val, $escape);
    }

    /**
     * @param string $key
     * @param array $val
     * @param bool $escape default TRUE
     * 
     * @return QueryBuilder
     */
    public function orWhereNotIn(string $key, array $val, $escape = true)
    {
        return $this->orWhere($key, ' NOT IN ', $val, $escape);
    }

    /**
     * @param mixed $str
     * 
     * @return QueryBuilder
     */
    public function orWhereRaw($str)
    {
        return $this->orWhere($str);
    }

    /**
     * @param string $key
     * 
     * @return QueryBuilder
     */
    public function orWhereNotNull($key)
    {
        return $this->orWhere($key, ' IS NOT ', 'NULL');
    }

    /**
     * @param string $key
     * @return QueryBuilder
     */
    public function orWhereNull($key)
    {
        return $this->orWhere($key, ' IS ', 'NULL');
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string|array $field Field name
     * 
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
     * 
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
     * 
     * @return QueryBuilder Self reference
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $this->order = (empty($this->order)) ? 'ORDER BY ' : $this->order . ',';

        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $field[$key] = $value . ' ' . $direction;
            }
        } else {
            $field = ($field ?? $this->getPrimaryKey()) . ' ' . $direction;
        }

        $fields = (is_array($field)) ? implode(', ', $field) : $field;

        $this->order .= $fields;

        return $this;
    }

    /**
     * Adds fields to group by.
     *
     * @param string|array $field Field name or array of field names
     * 
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
     * 
     * @return QueryBuilder Self reference
     */
    public function having($field, $value = null)
    {
        $join = (empty($this->having)) ? 'HAVING ' : '';
        if (is_array($field)) {
            $thisModel = $this;
            $fields = array_map(function ($key, $value) use ($thisModel) {
                $chain = is_numeric($key) ? $thisModel->primaryKey . " = " . $thisModel->quote($value) : "$key = " . $thisModel->quote($value);
                return $chain;
            }, array_keys($field), array_values($field));
            $join .= implode(',', $fields);
        } else {
            $join .= !empty($value) ? $field . " = " . $this->quote($value) : $field;
        }
        $this->having .= $join;

        return $this;
    }

    /**
     * Adds a limit to the query.
     *
     * @param int $limit Number of rows to limit
     * @param int $offset Number of rows to offset
     * 
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
     * 
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
     * @param int $offset
     * @return QueryBuilder
     */
    public function skip($offset)
    {
        $this->offset($offset);
        return $this;
    }

    /**
     * @param int $limit
     * @return QueryBuilder
     */
    public function take($limit)
    {
        $this->limit($limit);
        return $this;
    }

    /**
     * Sets the distinct keyword for a query.
     * 
     * @param bool $value
     * 
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
     * 
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
     * Sets a not between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     * 
     * @return QueryBuilder
     */
    public function notBetween($field, $value1, $value2)
    {
        $this->where(sprintf(
            '%s NOT BETWEEN %s AND %s',
            $field,
            $this->quote($value1),
            $this->quote($value2)
        ));
        return $this;
    }

    /**
     * Sets a or between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     * 
     * @return QueryBuilder
     */
    public function orBetween($field, $value1, $value2)
    {
        $this->orWhere(sprintf(
            '%s BETWEEN %s AND %s',
            $field,
            $this->quote($value1),
            $this->quote($value2)
        ));
        return $this;
    }

    /**
     * Sets a or not between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     * 
     * @return QueryBuilder
     */
    public function orNotBetween($field, $value1, $value2)
    {
        $this->orWhere(sprintf(
            '%s NOT BETWEEN %s AND %s',
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
     * 
     * @return QueryBuilder Self reference
     */
    public function select($fields = '*', $limit = null, $offset = null)
    {
        $this->checkTable();

        $fields = is_array($fields) && !empty($fields) ? implode(',', $fields) : (is_string($fields) ? $fields : '*');
        
        $this->limit($limit, $offset);
        
        if ($fields === "*" && !empty($this->selectFields))
            return $this;

        $this->selectFields .= !empty($this->selectFields) ? ", " . $fields : $fields;

        return $this;
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values or array of keys to insert
     * @param array $values Array of values to use prepared statement
     * 
     * @return bool|int
     */
    public function insert(array $data = [], $values = [])
    {
        $this->checkTable();

        if (empty($data))
            return false;

        if (!empty($values)) {
            $keys = implode(',', array_values($data));
            $vals = str_repeat("? , ", count($values));
        } else {
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

        return ($this->execute($values))->ok;
    }

    /**
     * Builds an update query.
     *
     * @param array|\stdClass $data Array of keys and values or object of type \stdClass
     * @param int|null $id 
     * 
     * @return bool 
     */
    public function update($data, $id = null)
    {
        $this->checkTable();
        $data = is_object($data) ? get_object_vars($data) : $data;

        $values = array();

        $id && $this->where($this->getPrimaryKey() . " = " . $id);

        if (empty($this->where)) {
            if (!empty($data[$this->getPrimaryKey()])) {
                $this->where($this->getPrimaryKey() . " = " . $this->quote($data[$this->getPrimaryKey()]));
            } else {
                throw new Exception(text("Db.dontUse", ["UPDATE"]));
            }
        }

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                throw new Exception(text("Db.invalidDataArray"));
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

        return ($this->execute())->ok;
    }

    /**
     * Builds a delete query.
     *
     * @param string|array|int $where Where conditions
     * 
     * @return bool
     */
    public function delete($where = null)
    {
        $this->checkTable();

        $where && $this->where(is_int($where) ? $this->getPrimaryKey() . " = " . $where : $where);

        if (empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["DELETE"]));
        }

        $this->sql(array(
            'DELETE FROM',
            $this->getTable(),
            $this->where
        ));

        return ($this->execute())->ok;
    }

    /**
     * Gets or sets the SQL statement.
     *
     * @param string|array SQL statement
     * 
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
     * @param array $params
     * 
     * @throws Exception When database is not defined
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

        if (!empty($this->sql)) {
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
                throw new Exception(text("Db.databaseError", [$error]));
            }
        }

        $this->reset();

        return (object) ['ok' => ($bool && $this->getInsertID() ? $this->getInsertID() : $bool), 'result' => $result];
    }

    /**
     * get records from the db
     *
     * @param array|string $select
     * @param array|string|null $where
     * @param int|null $limit
     * @param int|null $offset
     * 
     * @return array
     */
    public function get($select = "*", $where = null, $limit = null, $offset = null)
    {
        if (!empty($where)) {
            $this->where($where);
        }
            echo 'not empty';

        if (empty($this->sql)) {
            $this->select($select);
        }
        
        $this->limit($limit)->offset($offset);

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
     *
     * @return object|null Row
     */
    public function one($fields = null, $where = null)
    {
        $data = $this->get($fields, $where, 1);

        return !empty($data) ? $data[0] : null;
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $field
     * @param string|array $value
     *
     * @return object|null Row
     */
    public function first($field = null, $value = null)
    {
        if (empty($this->order)) {
            $this->asc($field);
        }

        if ($field && $value) {
            is_array($value) ? $this->whereIn($field, $value) : $this->where($field, $value);
        }

        return $this->one();
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $field
     * @param string|array $value
     *
     * @return object|null Row
     */
    public function last($field = null, $value = null): object|null
    {
        if (empty($this->order)) {
            $this->desc($field);
        }

        if ($field && $value) {
            is_array($value) ? $this->whereIn($field, $value) : $this->where($field, $value);
        }

        return $this->one();
    }

    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * 
     * @return mixed Row value
     */
    public function value($name)
    {
        $row = $this->one();

        return !empty($row) ? (is_array($row) ? $row[$name] : $row->$name) : null;
    }

    /**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * 
     * @return number Row value
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
     * 
     * @return number Row value
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
     * 
     * @return number Row value
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
     * 
     * @return number Row value
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
     * 
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
     * 
     * @return string|int Quoted value
     */
    public function quote($value)
    {
        if ($value === null)
            return 'NULL';

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
     * @param string $field Search field
     * 
     * @return object|array|null Populated object
     */
    public function find($value = [], $field = null)
    {
        $field = is_null($field) ? $this->getPrimaryKey() : $field;

        if (!empty($value)) {
            if (!is_array($value) && ($this->getPrimaryKey() == $field || $this->isColumnExists($field))) {
                $this->where($field, $value);
            } else if (is_array($value)) {
                $this->whereIn($field, $value);
            }
        }
        
        return $field == $this->getPrimaryKey() && is_array($value) ? $this->get() : $this->one();
    }

    /**
     * Get the table name for this ER class.
     * 
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
        if (empty($this->primaryKey)) {
            $columns = $this->getTableInfo();

            foreach ($columns as $key => $column) {
                # code...
                if ($column["Key"] === "PRI") {
                    $this->primaryKey = $column["Field"];
                    break;
                }
            }
        }

        return $this->primaryKey;
    }

    /**
     * Check if field is part of the selected table
     * 
     * @return bool
     */
    public function isColumnExists($field)
    {
        $fieldFound = false;
        $columns = $this->getTableInfo();

        foreach ($columns as $key => $column) {
            # code...
            if ($field === $column["Field"]) {
                $fieldFound = true;
                break;
            }
        }

        return $fieldFound;
    }

    /**
     * Get the value of primaryKey
     * 
     * @return int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param int $fetchType - PDO FETCH constants
     * 
     * @return array $tableInfo
     */
    public function getTableInfo($fetchType = PDO::FETCH_ASSOC)
    {
        if (empty($this->tableInfo)) {
            $stmt = self::$db->prepare(
                "SHOW COLUMNS FROM " . ($this->getTable()) . ";"
            );
            $stmt->execute();
            $this->tableInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $fetchType === PDO::FETCH_OBJ ? array_map(function ($field) {
            return (object) $field;
        }, $this->tableInfo) : $this->tableInfo;
    }

    /**
     * Get $last_query dernière requête sql
     *
     * @return string
     */
    public function getLastQuery()
    {
        return $this->last_query;
    }

    /**
     * Set $last_query dernière requête sql
     *
     * @param string $last_query  $last_query dernière requête sql
     *
     * @return self
     */
    public function setLastQuery(string $last_query)
    {
        $this->last_query = $last_query;

        return $this;
    }

    /**
     * Get $num_rows
     *
     * @return int
     */
    public function getNumRows()
    {
        return $this->num_rows;
    }

    /**
     * Get $insert_id id dernièrement ajouté
     *
     * @return int|string
     */
    public function getInsertID()
    {
        return $this->insert_id;
    }

    /**
     * Get $affcted_rows
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * Undocumented function
     *
     * @todo i need help here 
     * @return \Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): \Traversable
    {
        # code...
        $data = $this->get();
        return new \ArrayIterator($data ?? []);
    }

    /**
     * Set the value of returnType
     *
     * @param string $returnType
     *
     * @return self
     */
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * Set the value of primaryKey
     *
     * @param string $primaryKey
     *
     * @return self
     */
    public function setPrimaryKey(string $primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Set $insert_id id dernièrement ajouté
     *
     * @param int|string $insert_id  $insert_id id dernièrement ajouté
     *
     * @return self
     */
    public function setInsertId($insert_id)
    {
        $this->insert_id = $insert_id;

        return $this;
    }

    /**
     * Set $num_rows
     *
     * @param int $num_rows  $num_rows
     *
     * @return self
     */
    public function setNumRows(int $num_rows)
    {
        $this->num_rows = $num_rows;

        return $this;
    }

    /**
     * Set $affcted_rows
     *
     * @param int $affected_rows  $affcted_rows
     *
     * @return self
     */
    public function setAffectedRows(int $affected_rows)
    {
        $this->affected_rows = $affected_rows;

        return $this;
    }

    /**
     * Get $db connection
     *
     * @return \PDO
     */
    public function getDb()
    {
        return self::$db;
    }

    /**
     * Build the where clause
     * 
     * @param array|string $key
     * @param null|string|null $operator
     * @param array|string|null $val
     * @param string $link default AND 
     * @param bool $escape default TRUE
     * 
     * @throws Exception
     * @return QueryBuilder
     */
    protected function buildWhere($key, $operatorOrValue = null, $val = null, $link = ' AND ', $escape = true)
    {
        if (empty($key)) {
            throw new Exception(text("Db.emptyWhere"));
        }

        $this->where .= empty($this->where) ? ' WHERE ' : '';
        $glue = !empty($this->where) && trim(strtolower($this->where)) != 'where' ? $link : '';
        
        if (is_array($key) ) {
            $key = array_filter($key);
            $counter = count($key);
            
            if ($counter !== count($key, COUNT_RECURSIVE)) {
                // Handle multidimensional array
                $this->where .= '(';

                foreach ($key as $k => $condition) {
                    if (is_array($condition)) {
                        $this->handleSingleArrayCondition($condition, $escape, $link);
                    }
                }
                $this->where .= ') ';

            } else {
                // Handle single condition
                $this->handleSingleArrayCondition($key, $escape, $link);
            }
        } else {
            if (is_array($operatorOrValue) && !empty($operatorOrValue)) {
                list($operatorOrValue, $val) = ['IN', '(' . implode(',', array_map(array($this, 'quote'), $operatorOrValue)) . ')'];
            }

            if ((!is_array($operatorOrValue) && !is_null($operatorOrValue)) && is_null($val)) {
                list($operatorOrValue, $val) = ['=', $this->quote($operatorOrValue)];
            }

            $operator = is_null($operatorOrValue) ? null : strtoupper(trim($operatorOrValue));

            if (in_array($operator, self::OPERATORS)) {
                if ($val === 'NULL') {
                    $val = null;
                    $operatorOrValue = $operator.' NULL';
                } else {
                    if (is_array($val)) {
                        if (stripos($operator, 'in') !== false) {
                            $val = '(' . implode(',', array_map(array($this, 'quote'), $val)) . ')';
                        }
                        if (stripos($operator, 'between') !== false) {
                            $val = implode(',', array_map(array($this, 'quote'), $val));
                        }
                    }
                }
            }

            $this->where .= rtrim($glue . $key . ' ' . $operatorOrValue . ' ' . $val);
        }
        
        return $this;
    }

    protected function addWhereArrayCondition(array $condition, $escape = true, &$link = ' AND ') {
        $this->where .= empty($this->where) ? ' WHERE ' : '';
        $glue = !empty($this->where) && trim(strtolower($this->where), ' (') != 'where' ? $link : '';


        $firstArrayKey = key($condition);
        
        if (count($condition) === 3 && is_numeric($firstArrayKey)) {
            list($field, $operator, $value) = $condition;
            $operator = strtoupper(trim($operator));

            if (!in_array($operator, self::OPERATORS)) {
                throw new Exception(text("Db.unknownOperator", [$operator]));
            }

            $value = $escape && !is_numeric($value) ? $this->quote($value) : $value;
            $this->where .= $glue . ' ' . $field . ' ' . $operator . ' ' . $value;
        } elseif (count($condition) === 1) {
            $field = key($condition);
            $value = $condition[$field];
            $value = $escape && !is_numeric($value) ? $this->quote($value) : $value;
            $this->where .= $glue . ' ' . $field . ' = ' . $value;
        } else {
            throw new Exception(text("Db.malFormedWhereArray", [print_r($condition, true)]));
        }
    }

    protected function handleSingleArrayCondition(array $condition, $escape = true, &$link = ' AND ') {
        $firstArrayKey = key($condition);
        if (is_numeric($firstArrayKey)) {
            $this->addWhereArrayCondition($condition, $escape, $link);
        } else {
            foreach ($condition as $k => $value) {
                # code...
                $this->addWhereArrayCondition([$k => $value], $escape, $link);
            }
        }
    }
    
}