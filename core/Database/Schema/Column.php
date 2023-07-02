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

class Column
{
	const OPTION_UNSIGNED = 'UNSIGNED';
	const OPTION_ZEROFILL = 'ZEROFILL';

	/** @var string */
	private $name;

	/** @var int */
	private $length;

	/** @var string|null */
	private $type;

	/** @var array<scalar> */
	private $parameters = [];

	/** @var array<string, scalar|null> */
	private $options = [];

	/** @var bool */
	private $nullable = FALSE;

	/** @var bool */
	private $autoIncrement = FALSE;

	/** @var scalar|null */
	private $defaultValue;

	/** @var string|null */
	private $comment;

	/**
	 * to enable chaining with the Table
	 * @var Table 
	 */
	private $caller;


	/**
	 * @param  string $name
	 * @param  string|null $type
	 * @param  array<scalar>|null $parameters
	 * @param  array<string|int, scalar|null> $options  [OPTION => VALUE, OPTION2]
	 * @param  Table|null $caller
	 */
	public function __construct($name, $type, array $parameters = NULL, array $options = [], Table $caller = null)
	{
		$this->name = $name;
		$this->caller = $caller;
		list($sqlType, $length) = Table::matchTypeLength($type);
		$this->type($sqlType)->length($length);
		$this->params($parameters);
		$this->setOptions($options);
	}

	/**
	 * Call this method if you are using the method from Table class
	 * like addColumn
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
	 * @param  numeric|string|bool $length
	 * @return self
	 */
	public function length($length)
	{
		$this->length = $length;
		return $this;
	}


	/**
	 * @return numeric|string|bool
	 */
	public function getLength()
	{
		return $this->length ?? false;
	}

	/**
	 * @param  string|null $type
	 * @return self
	 */
	public function type($type)
	{
		$this->type = strtoupper($type);
		return $this;
	}


	/**
	 * @param  scalar|array<scalar>|null $parameters
	 * @return self
	 */
	public function params($parameters)
	{
		if ($parameters === NULL) {
			$parameters = [];

		} elseif (!is_array($parameters)) {
			$parameters = [$parameters];
		}

		$this->parameters = $parameters;
		return $this;
	}


	/**
	 * @param  string $option
	 * @param  scalar|null $value
	 * @return self
	 */
	public function addOption($option, $value = NULL)
	{
		$this->options[$option] = $value;
		return $this;
	}


	/**
	 * @param  array<string|int, scalar|null> $options
	 * @return self
	 */
	public function setOptions(array $options)
	{
		$this->options = [];

		foreach ($options as $k => $v) {
			if (is_int($k)) {
				$this->options[(string) $v] = NULL;

			} else {
				$this->options[$k] = $v;
			}
		}

		return $this;
	}


	/**
	 * @return array<string, scalar|null>
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	public function hasOption($name)
	{
		return array_key_exists($name, $this->options);
	}


	/**
	 * @param  bool $nullable
	 * @return self
	 */
	public function nullable($nullable = TRUE)
	{
		$this->nullable = $nullable;
		return $this;
	}


	/**
	 * @param  bool $autoIncrement
	 * @return self
	 */
	public function autoIncrement($autoIncrement = TRUE)
	{
		$this->autoIncrement = $autoIncrement;
		return $this;
	}


	/**
	 * @param  scalar|null $defaultValue
	 * @return self
	 */
	public function default($defaultValue)
	{
		$this->defaultValue = $defaultValue;
		return $this;
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

    public function toSQL()
    {
        $sql = Schema::quoteIdentifier($this->getName()) . ' ';
		// If it's enum, we use the parameters
		$sql .= in_array(strtolower($this->type), ["enum", "set"]) ? 
					"ENUM(".join(",", array_map("Schema::quoteDescription", $this->parameters)).")" :
				$this->type. ($this->getLength() ? "(". $this->getLength() .")" : "" );

		// If it's null so it's NULL what do you exepect ?
        $sql .= $this->nullable ? " NULL " : " NOT NULL ";
		
		// quote the dafault value and if it nullable so default value is NULL right ?
        $default = $this->defaultValue ? (is_string($this->defaultValue) ? Schema::quoteDescription( $this->defaultValue ) : $this->defaultValue) ." " : ($this->nullable ? "NULL " : "");

        if(!empty($default)) {
            $sql .= " DEFAULT ". $default;
        }
        if ($this->autoIncrement) {
            $sql .= ' AUTO_INCREMENT ';
        }
		if(count($this->getOptions()) >= 1)
		{
			foreach ($this->getOptions() as $option => $value) {
				# code...
				$sql .= is_numeric($option) ? " ".strtoupper($value)." " : strtoupper($option). (in_array(strtolower($option), ["charset", "collate"]) ? "=" : " ") . $value." ";
			}
		}
        if ($this->comment != '')
        {
            $sql .= ' comment ' . Schema::quoteDescription( $this->comment );
        }
        return trim(preg_replace("/\s\s/", " ", $sql));
    }

	public function __toString()
	{
		return $this->toSQL();
	}


	/**
	 * Set to enable chaining with the Table
	 *
	 * @param  Table  $caller  to enable chaining with the Table
	 *
	 * @return  self
	 */ 
	public function setCaller(Table $caller)
	{
		$this->caller = $caller;

		return $this;
	}
}
