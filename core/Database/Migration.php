<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Database
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Database;

use Footup\Database\Schema\Schema;

abstract class Migration
{
    const UP = 'up';
    const DOWN = 'down';
    const EMPTY = 'empty';

	/**
	 * Schema Builder
	 */
	public Schema $schema;

	/**
	 * Migration table name
	 */
	public static $table = 'migrations';
    
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * 
     * @param Schema $schema
     * @param boolean $empty
     * @return bool|Schema
     */
    abstract protected function up(Schema $schema);

    /**
     * 
     * @param Schema $schema
     * @param boolean $empty
     * @return bool|string|Schema
     */
    abstract protected function down(Schema $schema);

    /**
     * 
     * @param Schema $schema
     * @param boolean $empty
     * @return bool|string|Schema
     */
    abstract protected function empty(Schema $schema);

    /**
     * 
     * @param string $action
     * @return bool|string|Schema
     */
    public function execute($action = null)
    {
        $result = false;

        switch($action)
        {
            case self::DOWN: $result = $this->down($this->schema); break;
            case self::EMPTY: $result = $this->empty($this->schema); break;
            case self::UP: $result = $this->up($this->schema); break;
        }

        return $result;
    }

    public function getSchema()
    {
        return $this->schema;
    }
}
