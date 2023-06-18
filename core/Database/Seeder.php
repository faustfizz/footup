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

use Footup\Database\DbConnection;
use InvalidArgumentException;
use PDO;

abstract class Seeder
{
    /**
     * Database Connection instance
     *
     * @var PDO
     */
    protected $db;

    /**
     * @var string
     */
    protected $namespace = "\\App\\Seed\\";

    /**
     * @var string
     */
    protected $table;

    /**
     * Seeder constructor.
     */
    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? DbConnection::getDb();
    }

    /**
     * @param string $name
     * @return self
     */
    protected function table(string $name)
    {
        $this->setTable($name);
        return $this;
    }

    /**
     * Loads the specified seeder and runs it.
     *
     * @throws InvalidArgumentException
     * @return self
     */
    protected function call(string $class)
    {
        $class = trim($class);

        if ($class === '') {
            throw new InvalidArgumentException('No seeder was specified.');
        }

        if (strpos($class, '\\') === false) {
            $path = APP_PATH."Seed/". str_replace('.php', '', $class) . '.php';

            if (! is_file($path)) {
                throw new InvalidArgumentException('The specified seeder is not a valid file: ' . $path);
            }

            // Assume the class has the correct namespace
            // @codeCoverageIgnoreStart
            $class = "\\App\\Seed\\" . $class;

            if (! class_exists($class, false)) {
                require_once $path;
            }
            // @codeCoverageIgnoreEnd
        }

        /** @var Seeder $seeder */
        $seeder = new $class();
        $seeder->run();

        unset($seeder);

        return $this;
    }

    /**
     * Run the database seeds. This is where the magic happens.
     *
     * Child classes must implement this method and take care
     * of inserting their data here.
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    abstract public function run();

    /**
     * Insert one row
     *
     * @param array<string, scalar> $columns
     * @return bool
     */
    protected function insert($columns)
    {
        if(empty($columns))
            throw new InvalidArgumentException("Columns Data should be an array of key => value");

        if(empty($this->table))
            throw new InvalidArgumentException('Table should be defined before, please call $this->table($tableName) !');


        $columnString = implode(', ',array_keys($columns));
        $placeholderValues = [];
        foreach($columns as $columnName => $value) {
            $placeholderValues[':'.$columnName]=$value;
        }

        $placeholderString = implode(', ',array_keys($placeholderValues));

        $sql = 'INSERT INTO `'.$this->table.'` ('.$columnString.') VALUES ('.$placeholderString.')';
        
        $stmt = $this->db->prepare($sql);

        if(!$stmt instanceof \PDOStatement) {
            throw new InvalidArgumentException(print_r($this->db->errorInfo()));
        }
        return $stmt->execute($placeholderValues);
    }

    /**
     * Insert multiple rows
     *
     * @param array<array<string, scalar>> $columns
     * @return bool
     */
    protected function insertBatch($columns)
    {
        if(empty($columns) || !is_array($columns[0]))
            throw new InvalidArgumentException("Columns Data should be on the form [[key => value]]");
            
        if(empty($this->table))
            throw new InvalidArgumentException('Table should be defined before, please call $this->table($tableName) !');


        $columnString = implode(', ',array_keys($columns[0]));
        $countPlaceholders = count($columns[0]);

        $sql = 'INSERT INTO `'.$this->table.'` ('.$columnString.') VALUES ('.trim(str_repeat("?,", $countPlaceholders), ',').')';
        
        $stmt = $this->db->prepare($sql);
        
        if(!$stmt instanceof \PDOStatement) {
            throw new InvalidArgumentException(print_r($this->db->errorInfo()));
        }
        $this->db->beginTransaction();
        foreach ($columns as $dataArry) {
            # code...
            $stmt->execute(array_values($dataArry));
        }
        $this->db->commit();
        return true;
    }

    /**
     * Get the value of table
     *
     * @return  string
     */ 
    protected function getTable()
    {
        return $this->table;
    }

    /**
     * Set the value of table
     *
     * @param  string  $table
     *
     * @return  self
     */ 
    protected function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }
}