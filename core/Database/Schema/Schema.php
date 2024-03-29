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
use Footup\Database\DbConnection;
use PDO;


/**
 * @method bool|string erase($tableName = null, $ifExists = true) drop the tables
 * @method bool|string drop($tableName = null, $ifExists = true) drop the tables
 * @method bool|string empty($tableName = null, $ifExists = true) empty the tables
 * @method bool|string truncate($tableName = null, $ifExists = true) empty the tables
 * @method bool|string build($tableName = null, $ifExists = true) create the tables
 * @method bool|string create($tableName = null, $ifExists = true) create the tables
 * @method bool|string execute($tableName = null, $ifExists = true) create the tables
 */
class Schema
{
	const BACKTICK = '`';
	const QUOTE = "'";
	
	/** @var PDO $db */
	private $db;

	/** @var array<string, Table>  [name => Table] */
	private $tables = [];

    /**
     * @param PDO|null
     */
    public function __construct(PDO $db = null)
    {
        $this->db = &$db;
    }

    /**
     * @param PDO|null
     * @return self
     */
    public static function new(PDO $db = null)
    {
        return new self($db);
    }

    public static function enableForeignKeyChecks()
    {
        DbConnection::getDb(true)->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    public static function disableForeignKeyChecks()
    {
        DbConnection::getDb(true)->query("SET FOREIGN_KEY_CHECKS = 0");
    }

	/**
	 * @param  string|Table $name
	 * @return Table
	 */
	public function table($name)
	{
		$table = NULL;

		if ($name instanceof Table) {
			$table = $name;
			$name = $table->getName();

		} else {
			$table = new Table($name, $this->db);
		}

		if (isset($this->tables[$name])) {
			throw new ErrorException("Table '$name' already exists.");
		}

		return $this->tables[$name] = $table;
	}

	/**
	 * @param  string|Table $name
	 * @param  \Closure $callback
	 * @return bool|string
	 */
	public static function createTable($name, \Closure $callback)
	{
		$table = NULL;
        $schema = self::new(DbConnection::getDb(true));

		if ($name instanceof Table) {
			$table = $name;
			$name = $table->getName();
		} else {
			$table = new Table($name, $schema->db);
		}

		if (isset($schema->tables[$name])) {
			throw new ErrorException("Table '$name' already exists.");
		}

        $callback($table);

        $schema->tables[$name] = $table;

		return $schema->doAction($name);
	}

	/**
	 * @param  string $name
	 * @return Table|null
	 */
	public function getTable($name)
	{
		if (isset($this->tables[$name])) {
			return $this->tables[$name];
		}

		return NULL;
	}

	/**
	 * @return Table[]
	 */
	public function getTables()
	{
		return $this->tables;
	}


    /**
     * return the $id quoted with BACKTICK
     * @param string $id the id to quote
     * @return string column name (quoted)
     */
    public static function quoteIdentifier($id)
    {
        if (empty($id)) {
            return '';
        }

        return self::BACKTICK . $id . self::BACKTICK;
    }


    /**
     * @param string $text
     * @return string quoted $text
     */
    static function quoteDescription($text)
    {
        return self::QUOTE . str_replace(self::QUOTE, self::QUOTE . self::QUOTE, $text) . self::QUOTE;
    }


    /**
     * @param string $text
     * @return string quoted $text
     */
    static function quoteEnumValue($text)
    {
        return self::quoteDescription($text);
    }

    /**
     * execute the execute method magically to Create, Drop or Truncate tables schema
     * @return \PDOStatement|bool|string
     */
    public function __call(string $action, $arguments)
    {
        return $this->doAction($arguments[0] ?? null, strtolower($action), $arguments[1] ?? true);
    }

    /**
     * execute the execute method magically to Create, Drop or Truncate tables schema
     * @return \PDOStatement|bool|string
     */
    public static function __callStatic(string $action, $arguments)
    {
        $schema = self::new(DbConnection::getDb(true));

        return $schema->doAction($arguments[0] ?? null, strtolower($action), $arguments[1] ?? true);
    }

    /**
     * execute sql to Create, Drop or Truncate tables schema
     *
     * @param string|Table|null $tableName if null that you selected all tables. What ? Yeah
     * @param string $action Choose one action to execute
     * @param bool $exists add "IF EXISTS" to the sql ()
     * @return bool|string
     */
    protected function doAction($tableName = null, $action = "create", $exists = true)
    {
        $tableName = $tableName instanceof Table ? $tableName->getName() : $tableName;
        $executed = $this->action($tableName, $action, $exists);

        if(!is_string($executed)){
            if(!empty($tableName)){
                unset($this->tables[$tableName]);
            }else{
                $this->tables = [];
            }
            return true;
        }else{
            return $executed;
        }
    }

    /**
     * execute sql to Create, Drop or Truncate tables schema
     *
     * @param string|null $tableName if null that you selected all tables. What ? Yeah
     * @param string $action Choose one action to execute
     * @param bool $exists add "IF EXISTS" to the sql ()
     * @return bool|string
     */
    private function action(string $tableName = null, $action = "create", $exists = true)
    {
        // get the real $action 
        switch(strtolower($action))
        {
            case 'erase':
            case 'drop':
                $action = "drop";
                break;
            case 'empty':
            case 'truncate':
                $action = "truncate";
                break;
            case 'build':
            case 'execute':
            case 'create':
                $action = "create";
                break;
            default:
                throw new ErrorException("Action '{$action}' not exists !");
        }
        if($tableName)
        {
            if($table = $this->getTable($tableName))
            {
                return $table->{$action}($exists);
            }
            if($action === "create")
            {
                throw new ErrorException("Table Object for `{$tableName}` not exists !");
            }else{
                $query = $action === 'truncate' ?
                    ("CREATE TABLE IF NOT EXISTS {$tableName}; TRUNCATE TABLE {$tableName}") :
                    strtoupper($action) ." TABLE "  . ($exists ? ($action === 'create' ? "IF NOT EXISTS" : "IF EXISTS") : "" )  . " `{$tableName}`;";

                $exec = (bool)$this->db->query($query) ?:  $this->db->errorInfo()[2];
                if(is_string($exec)) return $exec;
                return true;
            }
        }
        foreach ($this->getTables() as $value) {
            /**
             * @var bool|string $exec
             */
            if(is_string($exec = $value->{$action}($exists)))
            {
                return $exec;
            }
        }
        return true;
    }

    /**
     * generate sql
     *
     * @return string
     */
    public function toSQL()
    {
        $output = "";
        foreach ($this->getTables() as $table) {
            # code...
            $output .= (string)$table;
        }
        return trim(preg_replace("/\s\s/", " ", $output));
    }

    public function __toString()
    {
        return $this->toSQL();
    }


    /**
     * @param string $value
     * @return string
     */
    static public function unQuote($value)
    {
        if (substr($value, 0, 1) == substr($value, -1) && substr($value, 0, 1) == '"') {
            $value = substr($value, 1, -1);
        }
        if (substr($value, 0, 1) == substr($value, -1) && substr($value, 0, 1) == self::BACKTICK) {
            $value = substr($value, 1, -1);
        }
        if (substr($value, 0, 1) == substr($value, -1) && substr($value, 0, 1) == "'") {
            $value = substr($value, 1, -1);
        }
        $value = str_replace("''", "'", $value);

        return trim($value);
    }

	/**
	 * Get the value of db
	 */ 
	public function getDb()
	{
		return $this->db;
	}
}