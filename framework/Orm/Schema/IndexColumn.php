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

class IndexColumn
{
	const ASC = 'ASC';
	const DESC = 'DESC';

	/** @var string */
	private $name;

	/** @var string */
	private $order;

	/** @var int|null */
	private $length;


	/**
	 * @param  string $name
	 * @param  string $order
	 * @param  int|null $length
	 */
	public function __construct($name, $order = self::ASC, $length = NULL)
	{
		$this->setName($name);
		$this->setOrder($order);
		$this->length($length);
	}


	/**
	 * @param  string $name
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  string $order
	 * @return self
	 */
	public function setOrder($order)
	{
		$order = (string) $order;

		if ($order !== self::ASC && $order !== self::DESC) {
			throw new ErrorException("Order type '$order' not found.");
		}

		$this->order = $order;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getOrder()
	{
		return $this->order;
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
     * @return string sql
     */
    public function toSQL()
    {
        return trim(Schema::quoteIdentifier($this->getName())
            . ($this->length ? ' (' . $this->getLength() . ')' : ''));
    }

	public function __toString()
	{
		return $this->toSQL();
	}
}
