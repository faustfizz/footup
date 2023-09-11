<?php

namespace Footup\Cli\Commands;

use ErrorException;
use Footup\Database\DbConnection;
use Footup\Database\Migration;
use Footup\Database\Schema\Schema;
use PDO, stdClass;

trait MigrateTrait
{

    /**
     * Get all migrations from the migrations tables
     * 
     * @return array<stdClass>
     */
    protected function getMigrations($filter = "all")
    {
        $options = $filter === "all" || empty($filter) ? ['pending', 'applied', 'dropped', 'emptied'] : ($filter === "up" ? ['pending', 'dropped'] : ($filter === "down" ? ['applied', 'emptied'] : ['applied']));

        $status = implode(",", array_map('\Footup\Database\Schema\Schema::quoteDescription', $options));

        $DB = DbConnection::getDb(true);
        $stmt = $DB->query("SELECT * FROM " . Schema::quoteIdentifier(Migration::$table) . ($filter ? " WHERE status in (" . $status . ")" : ""));
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param int $id
     * @param string $status
     * @throws ErrorException
     * @return bool|void
     */
    protected function updateMigrationStatus($id, $status)
    {
        $DB = DbConnection::getDb(true);

        if (in_array($status, ['pending', 'applied', 'dropped', 'emptied'])) {
            $stmt = $DB->query("UPDATE " . Schema::quoteIdentifier(Migration::$table) . " SET status = '$status' WHERE id = $id");
            if ($stmt) {
                return true;
            } elseif (isset($DB->errorInfo()[2])) {
                $this->app()->io()->error($DB->errorInfo()[2], true);
                return false;
            }
        } else {
            $this->app()->io()->error("Status should be one of [" . implode(',', ['pending', 'applied', 'dropped', 'emptied']) . "]", true);
            exit;
        }
    }

    /**
     * Run migration
     *
     */
    protected function runMigration($method)
    {
        $io = $this->app()->io();
        $method = strtolower($method);
        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        if (!in_array($method, ["up", "down", "empty"])) {
            $io->error("Status should be one of [" . implode(',', ['up', 'down', 'empty']) . "]", true);
            exit;
        }

        if (($migrations = $this->getMigrations()) === array()) {
            $io->error("No migration found.", true);
            exit;
        }

        $totalSuccess = 0;
        $total = count($migrations);

        // You want to run all pending migrations
        foreach ($migrations as $migration) {
            $classname = $this->classname;

            # code...
            $result = $exec = false;

            $fullClass = $this->namespace . ucfirst($migration->class);
            /**
             * @var string $class
             */
            $class = ucfirst($migration->class);
            $finalFilename = $migration->version . '_' . $class . '.php';

            switch ($method) {
                case "up":
                    if (!in_array($migration->status, ['pending', 'dropped']) && ucfirst($classname) === ucfirst($migration->class) || $migration->status === "applied") {
                        $this->app()->io()->warn("The migration file '$finalFilename' status is '" . $migration->status . "' and it should be in 'pending' or 'dropped' to be 'applied'")->eol();
                        $this->app()->io()->warn("So, We skip it !")->eol();
                        continue 2;
                    }
                    break;
                case "down":
                    if (!in_array($migration->status, ['applied', 'emptied']) && ucfirst($classname) === ucfirst($migration->class) || $migration->status === "dropped") {
                        $this->app()->io()->warn("The table of migration file '$finalFilename' should be in 'applied' and exists to be 'dropped'")->eol();
                        $this->app()->io()->warn("So, We skip it !")->eol();
                        continue 2;
                    }
                    break;
                case "empty":
                    if (!in_array($migration->status, ['applied']) && ucfirst($classname) === ucfirst($migration->class) || in_array($migration->status, ['emptied', 'dropped'])) {
                        $this->app()->io()->warn("The table of migration file '$finalFilename' should be in status 'applied' to be 'emptied'")->eol();
                        $this->app()->io()->warn("So, We skip it !")->eol();
                        continue 2;
                    }
                    break;
            }

            if (!empty($classname) && ucfirst($classname) !== ucfirst($migration->class)) {
                continue;
            }

            require_once(APP_PATH . "Migration/$finalFilename");
            /**
             * @var Migration $migrationClass
             */
            $migrationClass = new $fullClass($this->schema);
            $result = $migrationClass->execute($method);

            $table = !empty($class) ? strtolower($class) : null;

            if ($result instanceof Schema) {
                $exec = $method === "up" ? $result->create($table) : ($method === "down" ? $result->drop($table) : $result->empty($table));
            }
            if (is_string($result) || is_string($exec)) {
                $io->error((is_string($result) ? $result : $exec), true);
                exit;
            }
            if ($result === true || $exec === true) {
                $applied = $this->updateMigrationStatus($migration->id, ($method === "up" ? "applied" : ($method === "down" ? "dropped" : "emptied")));
                if ($applied) {
                    $totalSuccess += 1;
                    $this->generated[] = ($method === "up" ? end($expl) . "/Migration/$finalFilename is applied" : ($method === "down" ? "`$table` is dropped" : "`$table` is emptied"));
                } // display file into the console
            }

            if (!empty($classname) && ucfirst($classname) === ucfirst($migration->class))
                break;

        }

        if ($total && $totalSuccess) {
            array_unshift($this->generated, "Total $totalSuccess out of $total " . ($totalSuccess === 1 ? 'migration' : 'migrations') . " are " . ($method === "up" ? "applied" : ($method === "down" ? "dropped" : "emptied")));
        }

        return $this->generated;
    }

}