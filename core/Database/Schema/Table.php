<?php

/**
 * FOOTUP - 0.1.6-Alpha - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Database
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Database\Schema;
use ErrorException;


/**
 * @method Column date($name, $length = false) false to take default from database
 * @method Column year($name, $length = false) false to take default from database
 * @method Column month($name, $length = false) false to take default from database
 * @method Column datetime($name, $length = false) false to take default from database
 * @method Column timestamp($name, $length = false) false to take default from database
 * @method Column tinyint($name, $length = false) false to take default from database
 * @method Column smallint($name, $length = false) false to take default from database
 * @method Column bool($name, $length = false) false to take default from database
 * @method Column boolean($name, $length = false) false to take default from database
 * @method Column mediumint($name, $length = false) false to take default from database
 * @method Column int($name, $length = false) false to take default from database
 * @method Column bigint($name, $length = false) false to take default from database
 * @method Column enum($name, $parameters) $parameters to make ENUM($parameters[0], $parameters[1]...) from database
 * @method Column double($name, $length = false) $length as floating number for database
 * @method Column float($name, $length = false) $length as floating number for database
 * @method Column decimal($name, $length = false) $length as decimal number for database
 * @method Column blob($name, $length = false) $length false to take default from database
 * @method Column mediumblob($name, $length = false) $length false to take default from database
 * @method Column longblob($name, $length = false) $length false to take default from database
 * @method Column binary($name, $length = false) $length false to take default from database
 * @method Column text($name, $length = false) $length false to take default from database
 * @method Column json($name, $length = false) $length false to take default from database
 * @method Column longtext($name, $length = false) $length false to take default from database
 * @method Column mediumtext($name, $length = false) $length false to take default from database
 * @method Column char($name, $length = false) $length false to take default from database
 * @method Column varchar($name, $length = false) $length false to take default from database
 * @method Column string($name, $length = false) $length false to take default from database
 * @method Column tinytext($name, $length = false) $length false to take default from database
 */
class Table
{
	/** @var string */
	private $name;

	/** @var string|null */
	private $comment;

	/** @var array<string, Column>  [name => Column] */
	private $columns = [];

	/** @var array<string, Index>  [name => Index] */
	private $indexes = [];

	/** @var array<string, ForeignKey>  [name => ForeignKey] */
	private $foreignKeys = [];

	/** @var array<string, string>  [name => value] */
	private $options = [];

	/** @var PDO */
	private $db;

	/** @var bool */
	private $ifNotExist = TRUE;


	/**
	 * @param  string $name
	 */
	public function __construct($name, $db = null)
	{
		$this->name = $name;
		$this->db = $db;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  string|null $comment
	 * @return self
	 */
	public function comment($comment)
	{
		$this->comment = $comment;
		return $this;
	}


	/**
	 * @param  string $name
	 * @param  string $value
	 * @return self
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
		return $this;
	}


	/**
	 * @param  string $name
	 * @return self
	 */
	public function removeOption($name)
	{
		unset($this->options[$name]);
		return $this;
	}


	/**
	 * @return array<string, string>
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @param  string|Column $name
	 * @param  string|null $type
	 * @param  array<scalar>|null $parameters
	 * @param  array<string, string> $options OPTION => NULL
	 * @return Column
	 */
	public function addColumn($name, $type = NULL, array $parameters = NULL, array $options = [])
	{
		$column = NULL;

		if ($name instanceof Column) {
			$column = $name;
			$name = $column->getName();

		} else {
			$column = new Column($name, $type, $parameters, $options, $this);
		}

		if (isset($this->columns[$name])) {
			throw new ErrorException("Column '$name' in table '{$this->getName()}' already exists.");
		}

		return $this->columns[$name] = $column;
	}

	/**
	 * @param  Column[] $olumns
	 * @return Table
	 */
	public function columns(array $columns)
	{
		/**
		 * @var Column[] $columns
		 */
		foreach ($columns as $column) {
			# code...
			$this->addColumn($column);
		}
		return $this;
	}


	/**
	 * @param  string|Column $name
	 * @return void
	 */
	public function removeColumn($name)
	{
		if ($name instanceof Column) {
			$name = $name->getName();
		}

		if (!isset($this->columns[$name])) {
			throw new ErrorException("Column '$name' in table '{$this->getName()}' not exists.");
		}

		unset($this->columns[$name]);
	}


	/**
	 * @param  string $name
	 * @return Column|null
	 */
	public function getColumn($name)
	{
		if (isset($this->columns[$name])) {
			return $this->columns[$name];
		}
		return NULL;
	}


	/**
	 * @return Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}


	/**
	 * @param  string|Index $name
	 * @param  string[]|string $columns
	 * @param  string $type
	 * @return Index
	 */
	public function addIndex($name, $columns = [], $type = Index::TYPE_INDEX)
	{
		$index = NULL;

		if ($name instanceof Index) {
			$index = $name;
			$name = $index->getName();

		} else {
			$index = new Index($name, $columns, $type, $this);
			$name = $index->getName();
		}

		if (isset($this->indexes[$name])) {
			throw new ErrorException("Index '$name' in table '{$this->getName()}' already exists.");
		}

		return $this->indexes[$name] = $index;
	}

	/**
	 * @param  Index[] $indexes
	 * 
	 * @return Table
	 */
	public function indexes(array $indexes)
	{
		/**
		 * @var Index[] $indexes
		 */
		foreach ($indexes as $index) {
			# code...
			$this->addIndex($index);
		}
		return $this;
	}


	/**
	 * @param  string|Index $name
	 * @return void
	 */
	public function removeIndex($name)
	{
		if ($name instanceof Index) {
			$name = $name->getName();
		}

		if (!isset($this->indexes[$name])) {
			throw new ErrorException("Index '$name' in table '{$this->getName()}' not exists.");
		}

		unset($this->indexes[$name]);
	}


	/**
	 * @param  string $name
	 * @return Index|null
	 */
	public function getIndex($name)
	{
		if (isset($this->indexes[$name])) {
			return $this->indexes[$name];
		}
		return NULL;
	}


	/**
	 * @return Index[]
	 */
	public function getIndexes()
	{
		return $this->indexes;
	}

	/**
	 * drop table
	 * 
	 * @param bool $ifNotExist
	 * @return bool|PDOStatement|string
	 */
	public function drop($ifNotExist = true)
	{
		if(empty($this->db))
		{
			throw new ErrorException("Cannot do a database action without database connection !");
		}
		return (bool)$this->db->query(($ifNotExist ? "CREATE TABLE IF NOT EXISTS {$this->getName()};" : "") ."DROP TABLE " . $this->getName()) ?:  $this->db->errorInfo()[2];
	}

	/**
	 * empty table
	 * 
	 * @param bool $ifNotExist
	 * @return bool|string
	 */
	public function truncate($ifNotExist = true)
	{
		if(empty($this->db))
		{
			throw new ErrorException("Cannot do a database action without database connection !");
		}
		return (bool)$this->db->query(($ifNotExist ? "CREATE TABLE IF NOT EXISTS {$this->getName()};" : "") ."TRUNCATE TABLE " .$this->getName()) ?:  $this->db->errorInfo()[2];
	}

	/**
	 * create table
	 * 
	 * @param bool $ifNotExist
	 * @return bool|string
	 */
	public function execute($ifNotExist = true)
	{
		$this->ifNotExist = $ifNotExist;

		if(empty($this->db))
		{
			throw new ErrorException("Cannot do a database action without database connection !");
		}
		return (bool)$this->db->query($this->toSQL()) ?: $this->db->errorInfo()[2];
	}


	/**
	 * @param  string|ForeignKey $name
	 * @param  string[]|string $columns
	 * @param  string $targetTable
	 * @param  string[]|string $targetColumns
	 * @return ForeignKey
	 */
	public function addForeignKey($name, $columns = [], $targetTable = NULL, $targetColumns = [])
	{
		$foreignKey = NULL;

		if ($name instanceof ForeignKey) {
			$foreignKey = $name;
			$name = $foreignKey->getName();

		} else {
			$foreignKey = new ForeignKey($name, $columns, $targetTable, $targetColumns, $this);
			$name = $foreignKey->getName();
		}

		if (isset($this->foreignKeys[$name])) {
			throw new ErrorException("Foreign key '$name' in table '{$this->getName()}' already exists.");
		}

		return $this->foreignKeys[$name] = $foreignKey;
	}

	/**
	 * @param  ForeignKey[] $fks
	 * 
	 * @return Table
	 */
	public function foreignKeys(array $fks)
	{
		/**
		 * @var ForeignKey[] $fks
		 */
		foreach ($fks as $fk) {
			# code...
			$this->addForeignKey($fk);
		}
		return $this;
	}


	/**
	 * @param  string|ForeignKey $name
	 * @return void
	 */
	public function removeForeignKey($name)
	{
		if ($name instanceof ForeignKey) {
			$name = $name->getName();
		}

		if (!isset($this->foreignKeys[$name])) {
			throw new ErrorException("Foreign key '$name' in table '{$this->getName()}' not exists.");
		}

		unset($this->foreignKeys[$name]);
	}


	/**
	 * @param  string $name
	 * @return ForeignKey|null
	 */
	public function getForeignKey($name)
	{
		if (isset($this->foreignKeys[$name])) {
			return $this->foreignKeys[$name];
		}
		return NULL;
	}


	/**
	 * @return ForeignKey[]
	 */
	public function getForeignKeys()
	{
		return $this->foreignKeys;
	}


	/**
	 * @throws ErrorException
	 * @return void
	 */
	public function validate()
	{
		$tableName = $this->getName();

		if (empty($this->columns)) {
			throw new ErrorException("Table '$tableName' hasn't columns.");
		}

		$hasPrimaryIndex = FALSE;

		foreach ($this->getIndexes() as $index) {
			if ($index->getType() === Index::TYPE_PRIMARY) {
				if ($hasPrimaryIndex) {
					throw new ErrorException("d primary index in table '$tableName'.");
				}
				$hasPrimaryIndex = TRUE;
			}
		}
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 * @return Column
	 */
	public function __call($method, $arguments)
	{
		list($type, $length) = self::matchTypeLength($method, $arguments[1] ?? false);
		$weHaveLength = isset($arguments[1]);

		/**
		 * @var Column
		 */
		$column = $this->addColumn($arguments[0], $type);
		
		if(strtolower($type) === 'enum')
		{
			if($weHaveLength && !is_array($arguments[1]))
			{
				throw new ErrorException("Type ENUM/SET should have values as choice");
			}
			$column->params($arguments[1]);

			return $column;
		}
		
		return $column->length($length);
	}
	
	/**
     * get crud type
     *
     * @param string $type
     * @param int|bool $length
     * @return array
     */
    public static function matchTypeLength($type, $length = false)
    {
        switch($type) {
            case 'date':
				return [$type, $length];
            case 'year':
				return [$type, $length];
            case 'month':
				return [$type, $length];
            case 'datetime':
				return [$type, $length];
            case 'timestamp':
				return [$type, $length];
            case 'tinyint':
				return [$type, $length ?: 4];
            case 'bool':
            case 'boolean':
				return ["tinyint", 1];
            case 'smallint':
				return [$type, $length ?: 6];
            case 'mediumint':
				return [$type, $length ?: 9];
            case 'int':
				return [$type, $length ?: 11];
            case 'bigint':
				return [$type, $length ?: 20];
            case 'enum':
            case 'set':
				return ["ENUM", $length];
			case 'float':
            case 'double':
			case 'decimal':
				return ["double", ((string)$length) ?: "25,3"];
            case 'blob':
				return [$type, $length];
            case 'binary':
				return [$type, $length ?: 249];
            case 'text':
				return [$type, $length];
            case 'mediumblob':
            case 'mediumtext':
				return ["mediumtext", $length];
            case 'json':
            case 'longblob':
            case 'longtext':
				return ["longtext", $length];
            case 'char':
				return [$type, $length ?: 3];
			case 'tinytext':
				return [$type, $length ?: 2];
            case 'varchar':
			case 'string':
            default:
				return ["varchar", $length ?: 255];
        }
    }

	/**
	 * render the table as DDL
	 * @param string $newline
	 * @param string $columnSeparator
	 * @return string generated sql
	 */
	public function toSQL($newline = "\n", $columnSeparator = ",\n")
	{
		if (count($this->getColumns()) <= 0) {
			return '';
		}

		$items = array();
		foreach ($this->getColumns() as $column) {
			$items[] = (string)$column;
		}
		foreach ($this->getIndexes() as $index) {
			$items[] = (string)$index;
		}
		foreach ($this->getForeignKeys() as $fk) {
			$items[] = (string)$fk;
		}

		$output = 'CREATE TABLE '. ($this->ifNotExist ? 'IF NOT EXISTS ' : '') . Schema::quoteIdentifier($this->getName()) . ' (' . $newline;
		$output .= join($columnSeparator, $items) . $newline . ')';

		if(count($this->getOptions()) >= 1)
		{
			foreach ($this->getOptions() as $option => $value) {
				# code...
				$output .= is_numeric($option) ? " ".strtoupper($value)." " : strtoupper($option). (in_array(strtolower($option), ["engine", "charset", "collate"]) ? "=" : " ") . $value." ";
			}
		}else{
			$output .= " ENGINE=INNODB DEFAULT CHARSET=utf8";
		}

		if (strlen($this->comment) > 0) {
			$output .= ' COMMENT=' . Schema::quoteDescription($this->comment);
		}
		$this->ifNotExist = TRUE;

		return trim(preg_replace("/\s\s/", " ", $output.";"));
	}

	public function __toString()
	{
		return $this->toSQL();
	}
	
}
