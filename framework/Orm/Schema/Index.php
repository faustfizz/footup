<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Database
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Database\Schema;
use ErrorException;

class Index
{
	const TYPE_INDEX = 'INDEX';
	const TYPE_PRIMARY = 'PRIMARY';
	const TYPE_UNIQUE = 'UNIQUE';
	const TYPE_FULLTEXT = 'FULLTEXT';

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var IndexColumn[] */
	private $columns = [];

	/** @var Table */
	private $caller;


	/**
	 * @param  string $name
	 * @param  string[]|string $columns
	 * @param  string $type
	 * @param  Table|null $caller
	 */
	public function __construct($name, $columns = [], $type = self::TYPE_INDEX, Table $caller = null)
	{
		$this->name = $name;
		$this->caller = $caller;
		$this->setType($type);

		if (!is_array($columns)) {
			$columns = [$columns];
		}

		foreach ($columns as $column) {
			$this->addColumn($column);
		}
	}

	/**
	 * Call this method if you are using the method from Table class
	 * like addIndex
	 *
	 * @return Table
	 */
	public function chain()
	{
		return $this->caller;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  string $type
	 * @return self
	 */
	public function setType($type)
	{
		$type = (string) $type;
		$exists = in_array($type, [self::TYPE_INDEX, self::TYPE_PRIMARY, self::TYPE_UNIQUE, self::TYPE_FULLTEXT]);

		if (!$exists) {
			throw new ErrorException("Index type '$type' not found.");
		}

		$this->type = strtoupper($type);
		return $this;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param  IndexColumn|string $column
	 * @return IndexColumn
	 */
	public function addColumn($column)
	{
		if (!($column instanceof IndexColumn)) {
			$column = new IndexColumn($column);
		}

		return $this->columns[] = $column;
	}


	/**
	 * @return IndexColumn[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

    /**
     * @return string sql
     */
    public function toSQL()
    {
		$columns = array_map(function(IndexColumn $col){ return $col->getName(); }, $this->getColumns());

		$index = in_array($this->getType(), [self::TYPE_UNIQUE, self::TYPE_PRIMARY]) ? $this->getType()." KEY " : $this->getType();
        return trim($index. (in_array($this->getType(), [self::TYPE_UNIQUE, self::TYPE_PRIMARY]) ? "" : " ".$this->getName())." (" . join(',', array_map('\Footup\Database\Schema\Schema::quoteIdentifier', $columns) ). ") ");
    }

	public function __toString()
	{
		return $this->toSQL();
	}
}
