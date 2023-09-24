<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use Exception;
use Footup\Database\DbConnection;
use Footup\Html\Form;
use Footup\Orm\Traits\CastValue;
use Footup\Orm\Traits\Events;
use Footup\Orm\Traits\Fillable;
use Footup\Orm\Traits\Relations;
use Footup\Paginator\Paginator;
use Footup\Utils\Arrays\Arrayable;
use PDO;

/**
 * BaseModel of FOOTUP
 * 
 * @method ModelQueryBuilder reset()
 * @method ModelQueryBuilder from($table, $reset = true)
 * @method ModelQueryBuilder join($table, $fields, $type = 'INNER', $operator = " = ")
 * @method ModelQueryBuilder leftJoin($table, $fields, $operator = " = ")
 * @method ModelQueryBuilder rightJoin($table, $fields, $operator = " = ")
 * @method ModelQueryBuilder fullJoin($table, $fields, $operator = " = ")
 * @method ModelQueryBuilder where($key, $val = null, $operator = null, $link = ' AND ', $escape = true)
 * @method ModelQueryBuilder when($expression, \Closure $callback)
 * @method ModelQueryBuilder orWhere(array|string $key, $val = null, $operator = null, $escape = true)
 * @method ModelQueryBuilder whereIn($key, array $val, $escape = true)
 * @method ModelQueryBuilder whereNotIn($key, array $val, $escape = true)
 * @method ModelQueryBuilder whereRaw($str)
 * @method ModelQueryBuilder whereNotNull($key)
 * @method ModelQueryBuilder whereNull($key)
 * @method ModelQueryBuilder orWhereIn(array|string $key, array $val, $escape = true)
 * @method ModelQueryBuilder orWhereNotIn(array|string $key, array $val, $escape = true)
 * @method ModelQueryBuilder orWhereRaw($str)
 * @method ModelQueryBuilder orWhereNotNull($key)
 * @method ModelQueryBuilder orWhereNull($key)
 * @method ModelQueryBuilder asc(string|array $field)
 * @method ModelQueryBuilder desc(string|array $field)
 * @method ModelQueryBuilder orderBy(string|array $field, $direction = 'ASC')
 * @method ModelQueryBuilder groupBy(string|array $field)
 * @method ModelQueryBuilder having(string|array $field, $value = null)
 * @method ModelQueryBuilder limit($limit = null, $offset = null)
 * @method ModelQueryBuilder offset($offset, $limit = null)
 * @method ModelQueryBuilder skip($offset)
 * @method ModelQueryBuilder take($limit)
 * @method ModelQueryBuilder distinct($value = true)
 * @method ModelQueryBuilder between(string $field, $value1, $value2)
 * @method ModelQueryBuilder notBetween(string $field, $value1, $value2)
 * @method ModelQueryBuilder orBetween(string $field, $value1, $value2)
 * @method ModelQueryBuilder orNotBetween(string $field, $value1, $value2)
 * @method ModelQueryBuilder select($fields = '*', $limit = null, $offset = null)
 * @method bool|int insert(array $data = [])
 * @method bool delete($where = null)
 * @method ModelQueryBuilder|string sql($sql = null)
 * @method ModelQueryBuilder setDb($config = null, $init = true)
 * @method \PDO getDb()
 * @method object execute(array $params = [])
 * @method Collection get($select = "*", $where = null, $limit = null, $offset = null)
 * @method BaseModel|null one($fields = null, $where = null)
 * @method BaseModel|null first(string $field = null, $where = null)
 * @method BaseModel|null last(string $field = null, $where = null)
 * @method mixed value($name)
 * @method mixed min(string $field, $key = null)
 * @method mixed max(string $field, $key = null)
 * @method mixed sum(string $field, $key = null)
 * @method mixed avg(string $field, $key = null)
 * @method mixed quote($value)
 * @method Collection|BaseModel find($value = null, string $field = null)
 * @method array getTableInfo()
 * @method string getLastQuery()
 * @method ModelQueryBuilder setLastQuery(string $last_query)
 * @method int getNumRows()
 * @method int|string getInsertID()
 * @method int getAffectedRows()
 */
class BaseModel implements \IteratorAggregate, \JsonSerializable, Arrayable
{
    use Fillable, Events, Relations, CastValue;

    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string can be self | object | array -- self for BaseModel and it's the default
     */
    protected $returnType = 'self';

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
     * @var array
     */
    protected $originalData = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var ModelQueryBuilder
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
    public function __construct(array $data = null, $config = null, $init = true)
    {
        $this->setBuilder(new ModelQueryBuilder($this, DbConnection::setDb($config, $init)));

        if (!empty($data)) {
            $this->fill($data, true);
        }
        // allow callbacks
        $this->tmp_callbacks = $this->allow_callbacks;
    }

    /**
     * @param string $columns
     * @return int
     */
    public static function count($columns = "*")
    {
        return (new static)->getBuilder()->count($columns);
    }

    /**
     * @param array $data
     * @return BaseModel
     */
    public function fill(array $data, $original = false)
    {
        $fields = $this->getFieldNames();

        if ($original) {
            $this->setOriginalData($data);
        }

        foreach ($fields as $field) {

            if ($original == false && !$this->isFillable($field))
                continue;

            # code...
            $this->data[$field] = $data[$field] ?? null;

        }
        return $this;
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @return bool
     */
    public function insert(array $data = [])
    {
        empty($data) && $data = $this->getData();

        if (empty($data)) {
            throw new Exception(text("Db.emptyData", ['INSERT']));
        }

        $eventData = ['data' => $data];

        if ($this->tmp_callbacks) {
            $eventData = $this->trigger('beforeInsert', $eventData);
        }

        $inserted = $this->getBuilder()->insert(array_intersect_key($eventData['data'], $this->getFillableKeys()));

        $eventData['id'] = $this->getBuilder()->getInsertID();

        if ($inserted) {
            $this->setOriginalData($eventData["data"]);
            if ($this->tmp_callbacks) {
                $eventData = $this->trigger('afterInsert', $eventData);
                $eventData["data"][$this->getPrimaryKey()] = $eventData["id"];
            }
            $this->fill($eventData["data"]);
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
    public function update(array $data = [])
    {
        empty($data) && $data = $this->getData();

        if (empty($data)) {
            throw new Exception(text("Db.emptyData", ['UPDATE']));
        }

        $id = $this->id();

        if (empty($id) && empty($this->builder->where)) {
            throw new Exception(text("Db.noWhereRef"));
        }

        $eventData = [
            'id' => $id,
            'data' => $data,
        ];

        if ($this->tmp_callbacks) {
            $eventData = $this->trigger('beforeUpdate', $eventData);
        }

        if ($this->hasChanged()) {
            $executed = $this->getBuilder()->update(array_intersect_key($eventData['data'], $this->getFillableKeys()), $id);
        } else {
            // as nothing was changed, we don't throw error. just return a success to our user
            $executed = true;
        }

        $eventData['result'] = $executed;

        if ($executed) {
            $this->setOriginalData($eventData["data"]);
            if ($this->tmp_callbacks) {
                $eventData = $this->trigger('afterUpdate', $eventData);
                $eventData["data"][$this->getPrimaryKey()] = $eventData["id"];
            }
            $this->fill($eventData['data']);
        }

        $this->tmp_callbacks = $this->allow_callbacks;

        return $executed;
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
        $object = is_null($object) ? $this : $object;

        $this->from($object->getTable());

        $data = $object->getData();

        if ($fields !== null) {
            $keys = array_flip($fields);
            $data = array_intersect_key($data, $keys);
        }

        if ($this->id()) {
            return $this->update($data);
        } else {
            if ($bool = $this->insert($data)
            ) {
                $object->{$this->getPrimaryKey()} = $this->getInsertID();
            }
            return $bool;
        }
    }

    /**
     * Builds a delete query.
     *
     * @param string|array|int $where Where conditions
     * @return bool
     */
    public function delete($where = null)
    {
        if (empty($where) && $this->id()) {
            $where = $this->getPrimaryKey() . " = " . $this->getBuilder()->quote($this->id());
        }

        $eventData = [
            'id' => $this->id()
        ];

        if ($this->tmp_callbacks) {
            // Call the before event and check for a return
            $eventData = $this->trigger('beforeDelete', $eventData);
        }

        $executed = $this->getBuilder()->delete($where);

        if ($this->tmp_callbacks && $executed) {
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
     * @return int|string
     */
    public function id()
    {
        return $this->data[$this->getPrimaryKey()] ?? null;
    }

    /**
     * We may know if model data is changed
     *
     * @return boolean
     */
    public function hasChanged()
    {
        // if the length of original data and the current data is different, we have make a change
        if (count($this->originalData) != count($this->data)) {
            return true;
        }

        // if the length for the two arrays is the same we check for data value
        foreach ($this->data as $field => $value) {
            if (isset($this->originalData[$field]) && $value != $this->originalData[$field]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get fillables for insert or update action
     *
     * @return array
     */
    public function getFillableKeys()
    {
        $returnedKeys = $this->getRealFillableKeys($this->getFieldNames());

        return array_flip($returnedKeys);
    }

    /**
     * Undocumented function
     *
     * @param string $select
     * @param array|string $where
     * @param int $limit
     * @param int $offset
     * @return  Collection <int, BaseModel|array|object>
     */
    public function get($select = "*", $where = null, $limit = null, $offset = null)
    {
        if ($this->tmp_callbacks) {
            $eventData = $this->trigger('beforeFind', [
                'data' => [],
                'where' => $where,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }

        $data = $this->getBuilder()->get($select, $where, $limit, $offset);

        if ($this->tmp_callbacks && !empty($data)) {
            $eventData = $this->trigger('afterFind', [
                'data' => $data
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
     * @return  Collection<int, BaseModel|array|object>
     */
    public function paginate(int $perPage = null, string $pageName = 'page', int $page = 0)
    {
        $this->paginatorConfig = new \App\Config\Paginator();

        $this->per_page = $perPage ?? $this->per_page ?? $this->paginatorConfig->perPage;

        if ($pageName) {
            $this->paginatorConfig->pageName = $pageName;
        }


        $page = (int) request()->get($this->paginatorConfig->pageName, $page);
        $page = $page >= 1 ? $page : 1;

        $total = (int) $this->getBuilder()->count();

        // Store it in the Pager library so it can be
        // paginated in the views.
        $this->page_count = $total / $this->per_page;
        $this->current_page = $page;

        $offset = ($page - 1) * $this->per_page;

        $this->setPaginator(new Paginator($total, $this->per_page, $page, request()->url(), $this->paginatorConfig));

        return $this->get("*", null, (int) $this->per_page, (int) $offset);
    }

    /**
     * Get the table name for this ER class.
     * 
     * @access public
     * @return string
     */
    public function getTable()
    {
        if (empty($this->table))
            return $this->table = strtolower(basename(strtr(get_class($this), ['\\' => '/'])));

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
            $this->primaryKey = "id_" . strtolower(basename(strtr(get_class($this), ['\\' => '/'])));
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
        if (empty($this->returnType)) {
            // We use our default 
            $this->returnType = "self";
        }

        return $this->returnType;
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->castValue($name);
        }
        if (in_array($name, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany)))) {
            return $this->loadRelations($name);
        }
        if (property_exists($this->builder, $name)) {
            return $this->builder->{$name};
        }
    }

    public function __set($property, $val)
    {
        if ($this->isFillable($property))
            $this->data[$property] = $val;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}(...$arguments);
        }

        // Note: value of $name is case sensitive.
        if (!method_exists($this, $name) && preg_match('/^findBy/', $name) == 1) {
            // it's a findBy{fieldname} dynamic method
            $fieldname = lcfirst(substr($name, 6)); // remove find by
            $match = isset($arguments[0]) ? $arguments[0] : null;
            return $this->getBuilder()->find($match, $fieldname);
        }

        if (!method_exists($this, $name) && preg_match('/^firstBy/', $name) == 1) {
            // it's a findBy{fieldname} dynamic method
            $fieldname = lcfirst(substr($name, 7));
            $match = isset($arguments[0]) ? $arguments[0] : null;
            return $this->getBuilder()->first($fieldname, $match);
        }

        if (!method_exists($this, $name) && preg_match('/^lastBy/', $name) == 1) {
            // it's a findBy{fieldname} dynamic method
            $fieldname = lcfirst(substr($name, 6));
            $match = isset($arguments[0]) ? $arguments[0] : null;
            return $this->getBuilder()->last($fieldname, $match);
        }

        $setter = substr($name, 0, 3);
        $field = strtolower(substr($name, 3));

        if ($setter === "set" && in_array($field, $this->getFieldNames())) {
            $this->data[$field] = isset($arguments[0]) ? $arguments[0] : null;
            return $this;
        }

        if ($setter === "get" && in_array($field, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany)))) {
            return $this->loadRelations($field, (isset($arguments[0]) ? $arguments[0] : $this->per_page), (isset($arguments[1]) ? $arguments[1] : 0));
        }

        if (in_array($name, array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany)))) {
            return $this->loadRelations($name, (isset($arguments[0]) ? $arguments[0] : $this->per_page), (isset($arguments[1]) ? $arguments[1] : 0));
        }

        // Load the method from the ModelQueryBuilder magically
        if (method_exists($this->builder, $name)) {
            // it's a findBy{fieldname} dynamic method
            return $this->getBuilder()->{$name}(...$arguments);
        }

        throw new Exception(text("Db.undefinedMethod", [$name, get_class($this)]));
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $model = (new static);
        return $model->$method(...$args);
    }

    public function fieldTypes()
    {
        $fields = array();
        foreach ($this->getBuilder()->getTableInfo(PDO::FETCH_OBJ) as $field) {
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

            if (in_array(strtolower($_type), ['set', 'enum'])) {
                $opt = strtr($length, ["'" => ""]);
                $field->options = explode(",", $opt);
            }

            $field->maxLength = (int) $length;
            $field->label = ucwords(strtr($field->Field, ["_" => " "]));
            $field->placeholder = ucwords(strtr($field->Field, ["_" => " "]));
            $field->name = $field->Field;
            $field->id = 'field_' . $field->Field;
            $field->isPrimaryKey = $field->Key == "PRI" ? true : false;
            $field->type = $_type;
            $field->null = $field->Null == 'YES' ? true : false;
            $field->required = !$field->null;
            $field->extra = $field->Extra;
            $field->default = $field->Default === "current_timestamp()" ? date("Y-m-d H:i:s") : $field->Default;
            $field->crudType = $this->getCrudType($_type, $length);

            $fields[$field->Field] = $field;
        }

        return array_intersect_key($fields, $this->getFillableKeys());
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

    public function getForm($action = "#", $data = [], $print = false)
    {
        if (empty($data)) {
            $data = $this->getData();
        }

        $form = new Form($action ?? "#", $this->fieldTypes(), $data);
        return $form->build()->print($print);
    }

    /**
     * Find all model in the database.
     *
     * @param string $select
     * @param mixed $where
     * @param int $limit
     * @param int $offset
     * @return Collection<int, BaseModel|array|object>
     */
    public static function all($select = "*", $where = null, $limit = null, $offset = null)
    {
        // search for models
        /**
         * @var BaseModel
         */
        $model = (new static);
        return $model->get($select, $where, $limit, $offset);
    }

    /**
     * Get the value of builder
     *
     * @return  ModelQueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Set the value of builder
     *
     * @param  ModelQueryBuilder  $builder
     *
     * @return  self
     */
    public function setBuilder(ModelQueryBuilder $builder)
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

    public function getIterator()
    {
        return new \ArrayIterator($this->paginate());
    }

    /**
     * Get the value of originalData
     *
     * @return  array
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * Set the value of originalData
     *
     * @param  array  $originalData
     *
     * @return  self
     */
    public function setOriginalData(array $originalData)
    {
        $this->originalData = $originalData;

        return $this;
    }

    /**
     * Get the value of data
     *
     * @return  array
     */
    public function toArray()
    {
        if (empty($this->data)) {
            $object = $this->getBuilder()->last();
            if ($object) {
                $this->fill(($object instanceof BaseModel) ? $object->getData() : (array) $object);
            }
        }
        $relations = array_keys(array_merge($this->hasOne, $this->hasMany, $this->manyMany, $this->belongsTo, $this->belongsToMany));

        foreach ($relations as $key => $relationName) {
            # code...
            $this->data[$relationName] = $this->loadRelations($relationName);
        }
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Get the value of data
     *
     * @return  array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @param  array  $data
     *
     * @return  self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


}