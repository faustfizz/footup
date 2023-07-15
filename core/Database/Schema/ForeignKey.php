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

class ForeignKey
{
	const ACTION_RESTRICT = 'RESTRICT';
	const ACTION_NO_ACTION = 'NO ACTION';
	const ACTION_CASCADE = 'CASCADE';
	const ACTION_SET_NULL = 'SET NULL';

	/** @var string */
	private $name;

	/** @var string[] */
	private $columns = [];

	/** @var string|null */
	private $targetTable;

	/** @var string[] */
	private $targetColumns;

	/** @var string */
	private $onUpdateAction = self::ACTION_CASCADE;

	/** @var string */
	private $onDeleteAction = self::ACTION_CASCADE;

	/** @var Table */
	private $caller;


	/**
	 * @param  string $name
	 * @param  string[]|string $columns
	 * @param  string|null $targetTable
	 * @param  string[]|string $targetColumns
	 * @param  Table|null $caller
	 */
	public function __construct($name, $columns, $targetTable, $targetColumns, Table $caller = null)
	{
		$this->name = $name;
		$this->caller = $caller;
		$this->setTargetTable($targetTable);

		if (!is_array($columns)) {
			$columns = [$columns];
		}

		foreach ($columns as $column) {
			$this->addColumn($column);
		}

		if (!is_array($targetColumns)) {
			$targetColumns = [$targetColumns];
		}

		foreach ($targetColumns as $targetColumn) {
			$this->addTargetColumn($targetColumn);
		}
	}

	/**
	 * Call this method if you are using the method from Table class
	 * like addForeignKey
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
	 * Referenced column on the target table
	 *
	 * @param string $targetColumn
	 * @return self
	 */
	public function references(string $targetColumn) {
		$this->targetColumns = []; // reset this array as you are setting a column as target
		return $this->addTargetColumn($targetColumn);
	}

	/**
	 * set target table
	 *
	 * @param string $targetTable
	 * @return self
	 */
	public function on(string $targetTable) {
		return $this->setTargetTable($targetTable);
	}

	/**
	 * @param  string $column
	 * @return self
	 */
	public function addColumn($column)
	{
		$this->columns[] = $column;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}


	/**
	 * @param  string|null $targetTable
	 * @return self
	 */
	public function setTargetTable($targetTable)
	{
		$this->targetTable = $targetTable;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getTargetTable()
	{
		return $this->targetTable;
	}


	/**
	 * @param  string $targetColumn
	 * @return self
	 */
	public function addTargetColumn($targetColumn)
	{
		$this->targetColumns[] = $targetColumn;
		$this->targetColumns = array_unique($this->targetColumns);

		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getTargetColumns()
	{
		return $this->targetColumns;
	}


	/**
	 * @param  string $onUpdateAction
	 * @return self
	 */
	public function onUpdate($onUpdateAction)
	{
		if (!$this->validateAction($onUpdateAction)) {
			throw new ErrorException("Action '$onUpdateAction' is invalid.");
		}

		$this->onUpdateAction = $onUpdateAction;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getOnUpdate()
	{
		return $this->onUpdateAction;
	}


	/**
	 * @param  string $onDeleteAction
	 * @return self
	 */
	public function onDelete($onDeleteAction)
	{
		if (!$this->validateAction($onDeleteAction)) {
			throw new ErrorException("Action '$onDeleteAction' is invalid.");
		}

		$this->onDeleteAction = $onDeleteAction;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getOnDelete()
	{
		return $this->onDeleteAction;
	}


	/**
	 * @param  string $action
	 * @return bool
	 */
	private function validateAction($action)
	{
		return in_array($action, [self::ACTION_RESTRICT, self::ACTION_NO_ACTION, self::ACTION_CASCADE, self::ACTION_SET_NULL]);
	}

    public function toSQL()
    {
        $sql = "CONSTRAINT fk_" . join("_", [$this->getName(), $this->getTargetTable()]) . " FOREIGN KEY (" . 
					join(',', array_map('\Footup\Database\Schema\Schema::quoteIdentifier', $this->getColumns())) . 
				") REFERENCES " . Schema::quoteIdentifier($this->getTargetTable()) ." (" . 
					join(',', array_map('\Footup\Database\Schema\Schema::quoteIdentifier', $this->getTargetColumns())) . 
				") ON UPDATE " . $this->getOnUpdate() . " ON DELETE " . $this->getOnDelete();
		
        return trim(preg_replace("/\s\s/", " ", $sql));
    }

	public function __toString()
	{
		return $this->toSQL();
	}

	/**
	 * Set the value of caller
	 *
	 * @return  self
	 */ 
	public function setCaller(Table $caller)
	{
		$this->caller = $caller;

		return $this;
	}
}
