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

namespace Footup\Database\Seeder;
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
    protected $table;

    /**
     * Seeder constructor.
     */
    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? DbConnection::getDb();
    }

    public function table($name)
    {
        return $this->setTable($name);
    }

    /**
     * Loads the specified seeder and runs it.
     *
     * @throws InvalidArgumentException
     */
    public function call(string $class)
    {
        $class = trim($class);

        if ($class === '') {
            throw new InvalidArgumentException('No seeder was specified.');
        }

        if (strpos($class, '\\') === false) {
            $path = "./". str_replace('.php', '', $class) . '.php';

            if (! is_file($path)) {
                throw new InvalidArgumentException('The specified seeder is not a valid file: ' . $path);
            }

            // Assume the class has the correct namespace
            // @codeCoverageIgnoreStart
            // $class = APP_NAMESPACE . '\Database\Seeds\\' . $class;

            if (! class_exists($class, false)) {
                require_once $path;
            }
            // @codeCoverageIgnoreEnd
        }

        /** @var Seeder $seeder */
        $seeder = new $class();
        $seeder->run();

        unset($seeder);
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
    public function insert($columns)
    {
        if(empty($columns))
            throw new \InvalidArgumentException("Columns Data should be an array of key => value");

        if(empty($tihs->table))
            throw new \InvalidArgumentException('Table should be defined before, please call $this->table($tableName) !');


        $columnString = implode(', ',array_keys($columns));
        $placeholderValues = [];
        foreach($columns as $columnName => $value) {
            $placeholderValues[':'.$columnName]=$value;
        }

        $placeholderString = implode(', ',array_keys($placeholderValues));

        $sql = 'INSERT INTO `'.$this->table.'` ('.$columnString.') VALUES ('.$placeholderString.')';
        
        $stmt = $this->db->prepare($sql);

        if(!$stmt instanceof \PDOStatement) {
            throw new \InvalidArgumentException(print_r($this->db->errorInfo()));
        }
        return $stmt->execute($placeholderValues);
    }

    /**
     * Insert multiple rows
     *
     * @param array<array<string, scalar>> $columns
     * @return bool
     */
    public function insertBatch($columns)
    {
        if(empty($columns) || !is_array($columns[0]))
            throw new \InvalidArgumentException("Columns Data should be on the form [[key => value]]");
            
        if(empty($tihs->table))
            throw new \InvalidArgumentException('Table should be defined before, please call $this->table($tableName) !');


        $columnString = implode(', ',array_keys($columns[0]));
        $countPlaceholders = count($columns[0]);

        $sql = 'INSERT INTO `'.$this->table.'` ('.$columnString.') VALUES ('.str_repeat("?", $countPlaceholders).')';
        
        $stmt = $this->db->prepare($sql);
        
        if(!$stmt instanceof \PDOStatement) {
            throw new \InvalidArgumentException(print_r($this->db->errorInfo()));
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
    public function getTable()
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
    public function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }
}