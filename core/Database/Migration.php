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
     * @return bool|string|Schema
     */
    abstract protected function up();

    /**
     * @return bool|string|Schema
     */
    abstract protected function down();

    /**
     * @return bool|string|Schema
     */
    abstract protected function empty();

    public function enableForeignKeyChecks() 
    {
        $this->schema->getDb()->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    public function disableForeignKeyChecks() 
    {
        $this->schema->getDb()->query("SET FOREIGN_KEY_CHECKS = 0");
    }

    /**
     * 
     * @param string $action
     * @return bool|string|Schema
     */
    public function execute($action = null)
    {
        $result = false;
        $this->disableForeignKeyChecks();

        switch ($action) {
            case self::DOWN: $result = $this->down(); break;
            case self::EMPTY: $result = $this->empty(); break;
            case self::UP: $result = $this->up(); break;
        }

        $this->enableForeignKeyChecks();

        return $result;
    }

    public function getSchema()
    {
        return $this->schema;
    }
}
