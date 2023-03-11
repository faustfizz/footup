<?php

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use App\Config\Config;
use Exception;
use Footup\Html\Form;
use Footup\Paginator\Paginator;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;

class BaseModel
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
     * @var string $class classe courrante
     */
    protected $class;

    /**
     * @var string[] $db_type Type de connexion à la base
     */
    protected $db_types = array(
        'pdomysql', 'pdopgsql', 'pdosqlite'
    );

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
     * @var bool $allow_callbacks activer les évenements 
     */
	protected $allow_callbacks      = true;
	
    /**
     * @var bool $tmp_callbacks activer les évenements temporairement
     */
	protected $tmp_callbacks;

    
    /**
     * Permet de passer un array de la forme $data = [ 'data' => [] ] avant insertion
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
    protected $beforeInsert         = [];

    /**
     * Permet de passer un array de la forme $data = [ 'data' => [], 'where'    => ,'limit' =>, 'offset'    =>  ] 
     * avant recuperation
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeFind           = [];

    /**
     * Permet de passer un array de la forme $data = [ 'id' => $primaryKeyValue ] avant suppression
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $beforeDelete         = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [] ] avant modification
     * 
     * @var array
     */
	protected $beforeUpdate         = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [] ] après insertion
     * 
     * @var array
     */
	protected $afterInsert          = [];

    /**
     * Permet de passer un array de la forme $data = [ 'data' => [ ModelObjectFetched ] ] après recupération
     * les callbacks doivent obligatoirement retourner $data
     * 
     * @var array
     */
	protected $afterFind            = [];

    /**
     * Permet de passer un array de la forme $data = [ 'id' => $primaryKeyValue, 'result'   => bool ] 
     * après suppression
     * 
     * @var array
     */
	protected $afterDelete          = [];
    
    /**
     * Permet de passer un array de la forme $data = [ 'id' =>  $primaryKeyValue, 'data' => [], 'result'  => bool ] 
     * après modification
     * 
     * @var array
     */
	protected $afterUpdate          = [];

    /**
     * @var array|string $tableInfo informations de la table
     */
    protected $tableInfo  = [];

    /**
     * FRelationships
     *
     * @example # Use with arrays:
     *
     *      protected $hasOne = [
     *           'properties1' => [
     *                              'model' => 'Other_Model_1',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     */
    protected $hasOne        = [];

    /**
     * FRelationships
     *
     * @example # Use with arrays:
     * 
     *      protected $hasMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model_1',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     */
    protected $hasMany       = [];

    /**
     * FRelationships
     *
     * @example # Use with arrays:
     *
     *      protected $manyMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model_1',
     *                              'pivot' => 'Pivot_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field',
     *                              'pivot_foreign_key' => 'modelKey_in_pivot_table',
     *                              'pivot_local_key' => 'localKey_in_pivot_table',
     *                             ]
     *          ....................
     *      ];
     *
     */
    protected $manyMany      = [];

    /**
     * FRelationships
     *
     * @example # Use with arrays:
     *
     *     protected $belongsTo = [
     *           'properties1' => [
     *                              'model' => 'Other_Model_1',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field'
     *                             ]
     *          ....................
     *      ];
     */
    protected $belongsTo     = [];

    /**
     * FRelationships
     *
     * Use with arrays:
     * 
     *      protected $belongsToMany = [
     *           'properties1' => [
     *                              'model' => 'Other_Model_1',
     *                              'pivot' => 'Pivot_Model',
     *                              'foreign_key' => 'foreign_field',
     *                              'local_key' => 'local_field',
     *                              'pivot_foreign_key' => 'modelKey_in_pivot_table',
     *                              'pivot_local_key' => 'localKey_in_pivot_table',
     *                             ]
     *          ....................
     *      ];
     */
    protected $belongsToMany = [];

    /**
     * @var \App\Config\Paginator
     */
    public $paginatorConfig;

    /**
     * @var \Footup\Paginator\Paginator
     */

    public $paginator;
    /**
     * @var \Footup\Paginator\Paginator
     */
    public $pager;

    /**
     * Class constructor.
     */
    public function __construct($data = [], $init = true, $config = null)
    {
        self::setDb($init, $config);
        $this->getTable();
        $this->getPrimaryKey();
        // charger les relations
        // $this->loadRelations();


        if(!empty($data))
        {
            $this->fill($data);
        }
        // allow callbacks
        $this->tmp_callbacks = $this->allow_callbacks;
    }

    /**
     * @param array $data
     * @return BaseModel
     */
    public function fill(array $data) 
    {
        $fields = $this->getFieldNames();
        foreach ($fields as $field) {
            # code...
            if(isset($data[$field]))
                $this->$field = $data[$field];
                
        }
        return $this;
    }

    /**
     * Retrouve les données de l'objet class courant
     *
     * @param string $property
     * @return mixed|array
     */
    public function getAttributes($property = null) 
    {
        $data = array();
        foreach ($this->getFieldNames() as $column) {
            # code...
            if(!is_null($property) && $column == $property && $this->$column)
            {
                return $this->$column;
            }
            $data[$column] = $this->$column ?? null;
        }
        return $data;
    }

    /*** Core Methods ***/

    /**
     * @param string $sql SQL statement
     * @param string $input Input string to append
     * @return string New SQL statement
     */
    public function build($sql, $input)
    {
        return (strlen($input) > 0) ? ($sql . ' ' . $input) : $sql;
    }

    /**
     * Annalyse d'un url et convert à un objet.
     *
     * @param string $connection Connection string
     * @return array Connection information
     * @throws Exception For invalid connection string
     */
    public static function parseConnection($connection)
    {
        $url = parse_url($connection);

        if (empty($url)) {
            throw new Exception(text('Db.urlInvalid'));
        }

        $cfg = array();

        $cfg['db_type'] = isset($url['scheme']) ? $url['scheme'] : $url['path'];
        $cfg['db_host'] = isset($url['host']) ? $url['host'] : null;
        $cfg['db_name'] = isset($url['path']) ? substr($url['path'], 1) : null;
        $cfg['db_user'] = isset($url['user']) ? $url['user'] : null;
        $cfg['db_pass'] = isset($url['pass']) ? $url['pass'] : null;
        $cfg['db_port'] = isset($url['port']) ? $url['db_port'] : null;

        return $cfg;
    }

    /**
     * Checks whether the table property has been set.
     * @throws Exception
     */
    public function checkTable()
    {
        if (!$this->getTable()) {
            throw new Exception(text("Db.undefinedTable"));
        }
    }

    /**
     * Checks whether the class property has been set.
     * @throws Exception
     */
    public function checkClass()
    {
        if (!$this->class) {
            throw new Exception(text("Db.modelClassUndefined"));
        }
    }

    /**
     * Resets class properties.
     * 
     * @return BaseModel
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel
     */
    public function where($key, $val = null, $operator = null, $link = ' AND ', $escape = true)
    {
        $this->where .= (empty($this->where)) ? 'WHERE ' : '';

        if (is_array($key)) {
            $key = array_filter($key);
            foreach ($key as $k => $v) {
                $link = !empty($this->where) && !in_array(trim($this->where), ['WHERE', 'where']) ? $link : '';
                $this->where .= $link . $k . ' ' . trim($operator ?? '=') . ' ' . ($escape && !is_numeric($v) ? $this->quote($v) : $v);
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
     * @return BaseModel
     */
    public function whereOr(array|string $key, $val = null, $operator = null, $escape = true)
    {
        return $this->where($key, $val, $operator, ' OR ', $escape);
    }

    /**
     * @param array|string $key
     * @param array|string $val
     * @return BaseModel
     */
    public function whereIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' IN ', 'AND', $escape);
    }

    /**
     * @param $key
     * @param array $val
     * @return BaseModel
     */
    public function whereNotIn($key, array $val, $escape = true)
    {
        return $this->where($key, $val, ' NOT IN ', 'AND', $escape);
    }

    /**
     * @param $str
     * @param array|null $build_data
     * @param string $link
     * @return BaseModel
     */
    public function whereRaw($str)
    {
        return $this->where($str);
    }

    /**
     * @param $key
     * @return BaseModel
     */
    public function whereNotNull($key)
    {
        return $this->where($key, 'NOT NULL', ' IS ');
    }

    /**
     * @param $key
     * @return BaseModel
     */
    public function whereNull($key)
    {
        return $this->where($key, 'NULL', ' IS ');
    }

    // where OR

    /**
     * @param array|string $key
     * @param array|string $val
     * @return BaseModel
     */
    public function whereOrIn(array|string $key, array $val, $escape = true)
    {
        return $this->whereOr($key, $val, ' IN ', $escape);
    }

    /**
     * @param array|string $key
     * @param array $val
     * @return BaseModel
     */
    public function whereOrNotIn(array|string $key, array $val, $escape = true)
    {
        return $this->whereOr($key, $val, ' NOT IN ', $escape);
    }

    /**
     * @param $str
     * @param array|null $build_data
     * @param string $link
     * @return BaseModel
     */
    public function whereOrRaw($str)
    {
        return $this->whereOr($str);
    }

    /**
     * @param $key
     * @return BaseModel
     */
    public function whereOrNotNull($key)
    {
        return $this->whereOr($key, ' NOT NULL ', ' IS ');
    }

    /**
     * @param $key
     * @return BaseModel
     */
    public function whereOrNull($key)
    {
        return $this->whereOr($key, 'NULL', ' IS ');
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string $field Field name
     * @return BaseModel Self reference
     */
    public function asc($field)
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string $field Field name
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel Self reference
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
     * @return BaseModel
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
     * @return BaseModel
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
     * @return BaseModel Self reference
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
     * @return bool
     */
    public function insert(array $data = [])
    {
        $this->checkTable();

        if (empty($data)) return false;

        $eventData = ['data' => $data];

		if ($this->tmp_callbacks)
		{
			$eventData = $this->trigger('beforeInsert', $eventData);
            $data = $eventData['data'];
		}

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

        $eventData = [
			'id'     => $insert->ok,
			'data'   => $data
		];

		if ($this->tmp_callbacks && $insert->ok)
		{
            $data[$this->getPrimaryKey()] = $eventData["id"];
            $eventData["data"] = $data;
            $this->fill($data);
			$this->trigger('afterInsert', $eventData);
		}

        $this->tmp_callbacks = $this->allow_callbacks;

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
        $id = $this->{$pk} ?? null;

        if(is_null($id))
        {
            $this->{$pk} = $id = isset($data[$pk]) ? $data[$pk] : null;
            $this->where($pk ? ($pk . " = " . $id) : null);
        }else{
            $this->where($pk . " = " . $id);
        }
        
        if (empty($this->where) && is_null($id) || is_null($this->where)) {
            throw new Exception(text("Db.dontUse", ["UPDATE"]));
        }

        $eventData = [
			'id'   => $id,
			'data' => $data,
		];

		if ($this->tmp_callbacks)
		{
			$eventData = $this->trigger('beforeUpdate', $eventData);
            $data = isset($eventData['data']) && !empty($eventData['data']) ? $eventData['data'] : $data;
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

		$eventData = [
			'id'     => $id,
			'data'   => $data,
			'result' => $execute,
		];

        
		if ($this->tmp_callbacks && $execute)
		{
            $this->fill($data);
			$this->trigger('afterUpdate', $eventData);
		}

		$this->tmp_callbacks = $this->allow_callbacks;

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
        $id = $this->{$pk} ?? null;

        $eventData = [
            'id'    => is_int($where) ? $where : ($id ?? $this->insert_id)
        ];

        if (is_int($where) || is_null($where) && $this->insert_id || $eventData["id"]) {
            $this->{$pk} = $eventData["id"];
            $where = $pk . " = " . $eventData["id"];
        }


        if ($where !== null) {
            $this->where($where);
        }

        if (is_null($where) || empty($this->where)) {
            throw new Exception(text("Db.dontUse", ["DELETE"]));
        }

		if ($this->tmp_callbacks)
		{
			// Call the before event and check for a return
			$eventData = $this->trigger('beforeDelete', $eventData);
		}

        $this->sql(array(
            'DELETE FROM',
            $this->table,
            $this->where
        ));

        $execute = ($this->execute())->ok;

		if ($this->tmp_callbacks && $execute)
		{
            $eventData['result'] = $execute;

			// Call the before event and check for a return
			$this->trigger('afterDelete', $eventData);
		}

		$this->tmp_callbacks = $this->allow_callbacks;

        return $execute;
    }

    /**
     * Gets or sets the SQL statement.
     *
     * @param string|array SQL statement
     * @return BaseModel|string SQL statement
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
     * @param string|array|object $db Database connection string, array or object
     * @throws Exception For connection error
     */
    public static function setDb($init = true, $config = null)
    {
        if ($init && self::$db == null) {
            // Connection string
            if (is_string($config)) {
                return self::setDb(self::parseConnection($config));
            }
            // Connection information
            else if (is_array($config) || is_null($config)) {
                $Config = (new Config($config))->config;

                switch ($Config['db_type']) {
                    case 'pdopgsql':
                        $dsn = sprintf(
                            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                            $Config['db_host'],
                            isset($Config['db_port']) ? $Config['db_port'] : 5432,
                            $Config['db_name'],
                            $Config['db_user'],
                            $Config['db_pass']
                        );

                        return self::$db = new PDO($dsn);

                    case 'pdosqlite':
                        return self::$db = new PDO('sqlite:/' . $Config['db_name']);

                    case 'pdomysql':
                    default:
                        $dsn = sprintf(
                            'mysql:host=%s;port=%d;dbname=%s',
                            $Config['db_host'],
                            isset($Config['db_port']) ? $Config['db_port'] : 3306,
                            $Config['db_name']
                        );
                        return self::$db = new PDO($dsn, $Config['db_user'], $Config['db_pass']);
                }

                if (self::$db == null) {
                    throw new Exception(text("Db.undefinedDb"));
                }
            }
            // Connection object or resource
            else {
                throw new Exception(text("Db.unsupportedType"));
            }
        } else {
            return self::$db;
        }
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
            throw new Exception('Database is not defined.');
        }

        $result = null;

        $this->num_rows = 0;
        $this->affected_rows = 0;
        $this->insert_id = -1;
        $this->last_query = $this->sql;

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
        
        $res = ['ok' => ($bool && $this->insert_id ? $this->insert_id : $bool), 'result' => $result];
        return (object)$res;
    }

    /**
     * Return the PK for this record.
     * 
     * @access public
     * @return integer
     */
    public function id()
    {
        return $this->{$this->getPrimaryKey()};
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
        if ($this->tmp_callbacks)
		{
			$eventData = $this->trigger('beforeFind', [
                'data'      => [],
                'where'     => $where ?? $this->where ?? null,
				'limit'     => $limit ?? $this->limit ?? null,
				'offset'    => $offset ?? $this->offset ?? null
			]);
		}

        if (!is_null($eventData['where'])) {
            $this->where($eventData['where']);
        }

        if (empty($this->sql)) {
            $this->select($select, $limit, $offset);
        }

        $data = array();

        $execute = $this->execute();

        /**
         * @var \PDOStatement
         */
        $result = $execute->result;

        /**
         * @var BaseModel[]
         */
        $data = $result->fetchAll(PDO::FETCH_CLASS, get_class($this));

		if ($this->tmp_callbacks && $execute->ok)
		{
			$eventData = $this->trigger('afterFind', [
                'data'      => $data
            ]);
		}

		$this->tmp_callbacks = $this->allow_callbacks;

        return $eventData['data'];
    }
    
    /**
     * Fonction de pagination | paginate function
     *
     * @param integer|null $perPage
     * @param string $pageName
     * @param integer $page
     * @return BaseModel[]
     */
    public function paginate(int $perPage = null, string $pageName = 'page', int $page = 0)
	{
        $this->paginatorConfig = new \App\Config\Paginator();

        if(!$perPage)
        {
            $perPage = $this->paginatorConfig->perPage;
        }

        if($pageName)
        {
            $this->paginatorConfig->pageName = $pageName;
        }


        $page = (int)request()->get($this->paginatorConfig->pageName, $page);
		$page  = $page >= 1 ? $page : 1;

		$total = (int)$this->count();

		// Store it in the Pager library so it can be
		// paginated in the views.
		$perPage     = is_null($perPage) ? $this->per_page : $perPage;
		$this->page_count = $total / $perPage;
		$this->current_page = $page;

		$offset      = ($page - 1) * $perPage;

        $this->paginator = $this->pager = new Paginator($total, $perPage, $page, request()->url(), $this->paginatorConfig);

        return $this->get("*", null, $perPage, $offset);
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
    public function min($field, $key = null, $expire = 0)
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
    public function max($field, $key = null, $expire = 0)
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
    public function sum($field, $key = null, $expire = 0)
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
    public function avg($field, $key = null, $expire = 0)
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
     * @return mixed Row value
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
     * Loads properties for an object.
     *
     * @param object $object Class instance
     * @param array $data Property data
     * @return object Populated object
     */
    public function load($object, array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    /**
     * Finds and populates an object.
     *
     * @param int|string|array Search value
     * @param string $field Search value
     * @return object|array|null Populated object
     */
    public function find($value = null, $field = null)
    {
        $field = is_null($field) ? $this->getPrimaryKey() : $field;

        $this->from($this->table ?? $this->getTable(), false);

        if ($value !== null) {
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
    public function save($object = null, array $fields = null)
    {
        $object = is_null($object) ? $this : $object;

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
                $object->{$pk} = $this->insert_id;
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
        $object = is_null($object) ? $this : $object;

        $this->from($object->getTable());

        $pk = $object->getPrimaryKey();
        $id = $object->{$pk} ?? null;

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
        $class = new \ReflectionClass($this);
        $classVar = $class->getDefaultProperties();
        
        if (isset($classVar['table']))
            return $this->table = $classVar['table'];

        return $this->table = strtolower($class->getShortName());
    }

    /**
     * Get the value of primaryKey
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        $classVar = get_class_vars(get_class($this));
        
        if (isset($classVar['primaryKey']))
        {
            $this->primaryKey = $classVar['primaryKey'];
            return $this->primaryKey;
        }
            
        return $this->primaryKey;
    }

    public function __get($name)
    {
        if(in_array($name, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany))))
        {
            return $this->loadRelations($name);
        }
    }

    public function __set($property, $val)
    {
        return $this->{$property} = $val;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        if (!method_exists($this, $name) && preg_match('/^findBy/', $name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name, 6); // remove find by
            $match     = isset($arguments[0]) ? $arguments[0] : null;
            return $this->find($match, strtolower($fieldname));
        }

        if (!method_exists($this, $name) && preg_match('/^firstBy/', $name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name, 7);
            return $this->first(strtolower($fieldname));
        }

        if (!method_exists($this, $name) && preg_match('/^lastBy/', $name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name, 6);
            return $this->last(strtolower($fieldname));
        }

        $setter = substr($name, 0, 3);
        $field = strtolower(substr($name, 3));

        if($setter === "set" && in_array($field, $this->getFieldNames()))
        {
            $this->$field = isset($arguments[0]) ? $arguments[0] : null;
            return $this;
        }

        if($setter === "get" && in_array($field, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany))))
        {
            return $this->loadRelations($field, (isset($arguments[0]) ? $arguments[0] : $this->per_page), (isset($arguments[1]) ? $arguments[1] : 0));
        }

        if(in_array($name, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany))))
        {
            return $this->loadRelations($name, (isset($arguments[0]) ? $arguments[0] : $this->per_page), (isset($arguments[1]) ? $arguments[1] : 0));
        }

        throw new Exception(__CLASS__ . ' not such method [' . $name . ']');
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

    public function fieldTypes()
    {
        $fields = array();
        foreach (self::$db->query("SHOW COLUMNS FROM `{$this->getTable()}`")->fetchAll(\PDO::FETCH_OBJ) as $field) {
            $type = explode("(", $field->Type);
            
            $_type = $type[0];
            
            if (isset($type[1])) {
                if (substr($type[1], -1) == ')') {
                    $length = substr($type[1], 0, -1);
                } else {
                    list($length) = explode(" ", $type[1]);
                    $length = substr($length, 0, -1);
                }
            } else {
                $length = '';
            }

            if(in_array(strtolower($_type), ['set', 'enum']))
            {   $opt = strtr($length, ["'" => ""]);
                $fields[$field->Field]['options'] = explode(",", $opt);
            }

            $fields[$field->Field]['maxLength'] = (int)$length;
            $fields[$field->Field]['label'] = ucwords(strtr($field->Field, ["_" => " "]));
            $fields[$field->Field]['name'] = $field->Field;
            $fields[$field->Field]['id'] = $field->Field;
            $fields[$field->Field]['isPrimaryKey'] = $field->Key == "PRI" ? true : false;
            $fields[$field->Field]['type'] = $_type;
            $fields[$field->Field]['null'] = $field->Null == 'YES' ? true : false;
            $fields[$field->Field]['extra'] = $field->Extra;
            $fields[$field->Field]['default'] = $field->Default;
            $fields[$field->Field]['crudType'] = $this->getCrudType($_type, $length);
        }
        $results = $this->getTableInfo();
        $flds = array();
        foreach ($results as $num => $row) {
            $row = (array)$row;
            $flds[$row['Field']] = (object)(array_merge($row, $fields[$row['Field']]));
        }

        return $flds;
    }

    /**
     * Retrouve les noms de propriétés du model à partir de la base de données.
     *
     * @return array of available columns
     */
    public function getFieldNames()
    {
        $fields = [];
        foreach ($this->getTableInfo() as $column) {
            if (is_array($column)) {
                $fields[] = $column['Field'];
            }
        }
        return $fields;
    }

    /**
     * Get type of model property.
     *
     * @param string $field
     * @return mixed SQL type of property
     * @return bool
     */
    protected function getFieldType($field)
    {
        foreach ($this->getTableInfo() as $column) {
            if ($column['Field'] == $field) {
                return ($column['Type']);
            }
        }
        return false;
    }

    /**
     * get crud type
     *
     * @param string $type
     * @param int|string $length
     * @return string
     */
    protected function getCrudType($type, $length)
    {
        switch ($type) {
            case 'date':
            case 'year':
                $type = 'date';
                break;
            case 'month':
                $type = 'month';
                break;
            case 'datetime':
            case 'timestamp':
                $type = 'datetime';
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                $type = $length == 1 ? "radio" : "number";
                break;
            case 'enum':
            case 'set':
                $type = "select";
                break;
            case 'double':
            case 'float':
            case 'decimal':
                $type = "decimal";
                break;
            case 'char':
            case 'varchar':
            case 'string':
            case 'tinytext':
                $type = "text";
                break;
            case '252':
            case 'blob':
            case 'text':
            case 'json':
            case 'mediumtext':
            case 'longtext':
            default:
                $type = "textarea";
                break;
        }

        return $type;
    }

    /**
     * Try to match property value to the table column type
     *
     * @param string $field
     * @param mixed  $value  to be matched
     * @return mixed $value  with converted type
     *
     * @todo match all possible types properly
     */
    protected function matchType($field, $value)
    {
        $type = $this->getFieldType($field);
        $type = explode('(', $type);
        switch ($type[0]) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                $value = (int)$value;
                break;
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                $value = (string)$value;
                break;
            case 'double':
            case 'float':
            case 'decimal':
                $value = (float)$value;
                break;
            default:
                break;
        }
        return $value;
    }

    public function getForm($action = "#", $data = [], $print = false)
    {
        if(empty($data))
        {
            $data = $this->getAttributes();
        }

        $form = new Form($action, $this->fieldTypes(), $data);
        return $form->build()->print($print);
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
     * Find all model in the database.
     *
     * @param string $select
     * @param mixed $where
     * @param int $limit
     * @param int $offset
     * @return BaseModel[]
     */
    public static function all($select = "*", $where = null, $limit = null, $offset = null)
    {
        // search for models
        /**
         * @var BaseModel
         */
        $model = (new ReflectionClass(\get_called_class()))->newInstance();
        return $model->get($select, $where, $limit, $offset);
    }

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
    public function hasOne($relationConfig)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        $object = $class->where($foreign_key, $this->{$local_key})->one();
        return $object;
    }

    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    public function manyMany($relationConfig, $limit = null, $offset = null)
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

        $objects = $class->join($pivot->getTable()." pivot", "pivot.$pivot_foreign_key = ".$class->getTable().".$foreign_key")
                        ->join($this->table, $this->table.".$local_key = pivot.$pivot_local_key")
                        ->get($class->getTable().".*, pivot.*", "pivot.$pivot_local_key = ".$this->{$local_key}, $limit, $offset);

        return $objects;
    }

    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    public function hasMany($relationConfig, $limit = null, $offset = null)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        $objects = $class->get("*", [$foreign_key => $this->{$local_key}], $limit, $offset);
        return $objects;
    }

    /**
     * Get object in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel Model instance
     */
    public function belongsTo($relationConfig)
    {
        /**
         * @var BaseModel
         */
        $class = new $relationConfig['model']();
        $foreign_key = $relationConfig['foreign_key'];
        $local_key = $relationConfig['local_key'];

        $object = $class->where($foreign_key, $this->{$local_key})->one();
        return $object;
    }


    /**
     * Get all objects in relationship with.
     *
     * @param array $relationConfig
     * @return BaseModel[] Models
     */
    public function belongsToMany($relationConfig, $limit = null, $offset = null)
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

        $objects = $model->join($pivot->getTable()." pivot", "pivot.$pivot_foreign_key = ".$model->getTable().".$foreign_key")
                        ->join($this->table, $this->table.".$local_key = pivot.$pivot_local_key")
                        ->get($model->getTable().".*, pivot.*", "pivot.$pivot_local_key = ".$this->{$local_key}, $limit, $offset);

        return $objects;
    }

    /**
     * Undocumented function
     *
     * @param string $event
     * @param array $eventData
     * @return array
     */
    protected function trigger(string $event, array $eventData)
    {
        // Ensure it's a valid event
        if (! isset($this->{$event}) || empty($this->{$event}))
        {
            return $eventData;
        }

        foreach ($this->{$event} as $callback)
        {
            if (! method_exists($this, $callback))
            {
                throw new Exception("La méthode '{$callback}' n'existe pas dans la classe '".get_class($this)."'");
            }

            $eventData = $this->{$callback}($eventData);
        }

        return $eventData;
    }


	/**
	 * Set the value of tmp_callbacks
	 *
	 * @return  self
	 */ 
	public function allowCallbacks($value = true)
	{
		$this->tmp_callbacks = $value;
		return $this;
	}
}