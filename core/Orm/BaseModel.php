<?php

/**
 * FOOTUP - 0.1.5 - 03.2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use Exception;
use Footup\Html\Form;
use Footup\Paginator\Paginator;
use PDO;
use ReflectionClass;

/**
 * BaseModel of FOOTUP
 * 
 * @method QueryBuilder reset()
 * @method QueryBuilder from($table, $reset = true)
 * @method QueryBuilder join($table, $fields, $type = 'INNER', $operator = '=')
 * @method QueryBuilder eftJoin($table, $fields, $operator = '=')
 * @method QueryBuilder rightJoin($table, $fields, $operator = '=')
 * @method QueryBuilder fullJoin($table, $fields, $operator = '=')
 * @method QueryBuilder where($key, $val = null, $operator = null, $link = ' AND ', $escape = true)
 * @method QueryBuilder whereOr(array|string $key, $val = null, $operator = null, $escape = true)
 * @method QueryBuilder whereIn($key, array $val, $escape = true)
 * @method QueryBuilder whereNotIn($key, array $val, $escape = true)
 * @method QueryBuilder whereRaw($str)
 * @method QueryBuilder whereNotNull($key)
 * @method QueryBuilder whereNull($key)
 * @method QueryBuilder whereOrIn(array|string $key, array $val, $escape = true)
 * @method QueryBuilder whereOrNotIn(array|string $key, array $val, $escape = true)
 * @method QueryBuilder whereOrRaw($str)
 * @method QueryBuilder whereOrNotNull($key)
 * @method QueryBuilder whereOrNull($key)
 * @method QueryBuilder asc($field)
 * @method QueryBuilder desc($field)
 * @method QueryBuilder orderBy($field, $direction = 'ASC')
 * @method QueryBuilder groupBy($field)
 * @method QueryBuilder having($field, $value = null)
 * @method QueryBuilder limit($limit = null, $offset = null)
 * @method QueryBuilder offset($offset, $limit = null)
 * @method QueryBuilder distinct($value = true)
 * @method QueryBuilder between($field, $value1, $value2)
 * @method QueryBuilder select($fields = '*', $limit = null, $offset = null)
 * @method bool|int insert(array $data = [])
 * @method boll update($data)
 * @method bool delete($where = null)
 * @method QueryBuilder|string sql($sql = null)
 * @method QueryBuilder setDb($config = null, $init = true)
 * @method \PDO getDb()
 * @method object execute(array $params = [])
 * @method BaseModel[]|null get($select = "*", $where = null, $limit = null, $offset = null)
 * @method BaseModel|null one($fields = null, $where = null)
 * @method BaseModel|null first($field = null, $where = null)
 * @method BaseModel|null last($field = null, $where = null)
 * @method mixed value($name)
 * @method mixed min($field, $key = null)
 * @method mixed max($field, $key = null)
 * @method mixed sum($field, $key = null)
 * @method mixed avg($field, $key = null)
 * @method int|null count($field = '*')
 * @method mixed quote($value)
 * @method BaseModel|BaseModel[]|null find($value = null, $field = null)
 * @method bool save(BaseModel $object = null, array $fields = null)
 * @method bool remove($object = null)
 * @method array getTableInfo()
 * @method bool create(array $properties)
 * @method BaseModel[]|bool findOrCreate(array $properties = null)
 * @method string getLastQuery()
 * @method QueryBuilder setLastQuery(string $last_query)
 * @method int getNumRows()
 * @method int|string getInsertID()
 * @method int getAffectedRows()
 */
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
     * @var QueryBuilder
     */
    protected $builder;
    
    /**
     * @var \App\Config\Paginator
     */
    private $paginatorConfig;

    /**
     * @var \Footup\Paginator\Paginator
     */
    private $paginator;

    /**
     * Class constructor.
     */
    public function __construct($data = [], $config = null, $init = true)
    {
        DbConnection::setDb($config, $init);
        $this->setBuilder(new QueryBuilder($this));

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

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @return bool
     */
    public function insert(array $data = [])
    {
        if (empty($data)) return false;

        $eventData = ['data' => $data];

		if ($this->tmp_callbacks)
		{
			$eventData = $this->trigger('beforeInsert', $eventData);
            $data = $eventData['data'];
		}

        $inserted = $this->getBuilder()->insert($data);

        $eventData = [
			'id'     => $this->getBuilder()->getInsertID(),
			'data'   => $data
		];

		if ($this->tmp_callbacks && $inserted)
		{
            $data[$this->getPrimaryKey()] = $eventData["id"];
            $eventData["data"] = $data;
            $this->fill($data);
			$this->trigger('afterInsert', $eventData);
		}

        $this->tmp_callbacks = $this->allow_callbacks;

        return $inserted;
    }

    /**
     * Builds an update query.
     *
     * @param array $data Array of keys and values, or string literal
     * @return bool 
     */
    public function update($data)
    {
        if (empty($data)) return false;

        $pk = $this->getPrimaryKey();

        $this->{$pk} = $id = isset($data[$pk]) ? $data[$pk] : $this->{$pk};

        $eventData = [
			'id'   => $id,
			'data' => $data,
		];

		if ($this->tmp_callbacks)
		{
			$eventData = $this->trigger('beforeUpdate', $eventData);
            $data = isset($eventData['data']) && !empty($eventData['data']) ? $eventData['data'] : $data;
		}

        $executed = $this->getBuilder()->update($data);

		$eventData = [
			'id'     => $id,
			'data'   => $data,
			'result' => $executed,
		];

        
		if ($this->tmp_callbacks && $executed)
		{
            $this->fill($data);
			$this->trigger('afterUpdate', $eventData);
		}

		$this->tmp_callbacks = $this->allow_callbacks;

        return $executed;
    }

    /**
     * Builds a delete query.
     *
     * @param string|array|int $where Where conditions
     * @return bool
     */
    public function delete($where = null)
    {
        $pk = $this->getPrimaryKey();
        $id = $this->{$pk} ?? $this->getBuilder()->getInsertID();

        $eventData = [
            'id'    => is_int($where) ? $where : $id
        ];

		if ($this->tmp_callbacks)
		{
			// Call the before event and check for a return
			$eventData = $this->trigger('beforeDelete', $eventData);
		}

        $executed = $this->getBuilder()->delete($where);

		if ($this->tmp_callbacks && $executed)
		{
            $eventData['result'] = $executed;

			// Call the before event and check for a return
			$this->trigger('afterDelete', $eventData);
		}

		$this->tmp_callbacks = $this->allow_callbacks;

        return $executed;
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
                'where'     => $where,
				'limit'     => $limit,
				'offset'    => $offset
			]);
		}

        $data = $this->getBuilder()->get($select, $where, $limit, $offset);

		if ($this->tmp_callbacks && !empty($data))
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

        $this->per_page = $perPage ?? $this->paginatorConfig->perPage ?? $this->per_page;

        if($pageName)
        {
            $this->paginatorConfig->pageName = $pageName;
        }


        $page = (int)request()->get($this->paginatorConfig->pageName, $page);
		$page  = $page >= 1 ? $page : 1;

		$total = (int)$this->getBuilder()->count();

		// Store it in the Pager library so it can be
		// paginated in the views.
		$this->page_count = $total / $this->per_page;
		$this->current_page = $page;

		$offset      = ($page - 1) * $perPage;

        $this->setPaginator(new Paginator($total, $this->per_page, $page, request()->url(), $this->paginatorConfig));

        $data = $this->get("*", null, (int)$this->per_page, (int)$offset);

        /**
         * I wanted to set the paginator  aivalable with same on all paginated results classes
         */
        if(!empty($data))
        {
            $currentClass = $this;
            $data = array_map(function(BaseModel $modelWithData) use($currentClass){
                $modelWithData->page_count = $currentClass->page_count;
                $modelWithData->current_page = $currentClass->current_page;
                $modelWithData->per_page = $currentClass->current_page;
                $modelWithData->setPaginator($currentClass->getPaginator());
                return $modelWithData;
            }, $data);
        }
        return $data;
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
     * Get the table name for this ER class.
     * 
     * @access public
     * @return string
     */
    public function getTable()
    {
        $class = new ReflectionClass($this);
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
        if(property_exists($this->builder, $name))
        {
            return $this->builder->{$name};
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
            return $this->getBuilder()->find($match, strtolower($fieldname));
        }

        if (!method_exists($this, $name) && preg_match('/^firstBy/', $name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name, 7);
            return $this->getBuilder()->first(strtolower($fieldname));
        }

        if (!method_exists($this, $name) && preg_match('/^lastBy/', $name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name, 6);
            return $this->getBuilder()->last(strtolower($fieldname));
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

        // Load the method from the QueryBuilder magically
        if (method_exists($this->builder, $name)) {
            // it's a find_by_{fieldname} dynamic method
            return $this->getBuilder()->{$name}(...$arguments);
        }

        throw new Exception(__CLASS__ . ' not such method [' . $name . ']');
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $model = (new ReflectionClass(\get_called_class()))->newInstance();
        return $model->{$method}(...$args);
    }

    public function fieldTypes()
    {
        $fields = array();
        foreach ($this->getBuilder()->getDb()->query("SHOW COLUMNS FROM `{$this->getTable()}`")->fetchAll(PDO::FETCH_OBJ) as $field) {
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
        $results = $this->getBuilder()->getTableInfo();
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
        foreach ($this->getBuilder()->getTableInfo() as $column) {
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
     * @return bool|string
     */
    public function getFieldType($field)
    {
        foreach ($this->getBuilder()->getTableInfo() as $column) {
            if ($column['Field'] == $field) {
                return ($column['Type']);
            }
        }
        return "varchar";
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

    /**
     * Get the value of builder
     *
     * @return  QueryBuilder
     */ 
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Set the value of builder
     *
     * @param  QueryBuilder  $builder
     *
     * @return  self
     */ 
    public function setBuilder(QueryBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Get the value of paginator
     *
     * @return  \Footup\Paginator\Paginator
     */ 
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Set the value of paginator
     *
     * @param  \Footup\Paginator\Paginator  $paginator
     *
     * @return  self
     */ 
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }
}