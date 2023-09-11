<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;
use Footup\Database\DbConnection;
use Footup\Database\Migration;
use Footup\Database\Schema\Schema;

class Migrate extends Command
{
    public $scaffold = false;
    protected $table;
    protected $namespace = "App\\Migration";

    protected $replacements = array(
        "{class_name}" => null
    );
    protected $generated = [];

    public function __construct(App $cli)
    {
        $this
            ->argument('<filename>', 'The name of the migration class to generate')
            ->option('-f --force', 'Force override file', null, false)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <filename> </end> ## Generate the class without specifying anything than the name<eol/>'
            );

        $this->inGroup("Migration");

        $this->alias("mg-create");

        parent::__construct('migrate:create', 'Generate migration file', false, $cli);

        try {
            //code...
            DbConnection::getDb(true)->query("CREATE TABLE IF NOT EXISTS " . Schema::quoteIdentifier(Migration::$table) . "(
                `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `version` VARCHAR(250) NOT NULL,
                `class` VARCHAR(250) NOT NULL,
                `status` ENUM('pending', 'applied', 'dropped', 'emptied') NOT NULL DEFAULT 'pending',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        } catch (\Throwable $th) {
            // do nothing as we are in controller
        }
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io): void
    {
        try {
            //code...
            DbConnection::getDb(true);
        } catch (\Throwable $th) {
            //throw $th;
            $io->warn($th->getMessage() . ". It means you cannot run migrations commands")->eol();
            exit;
        }

        // Collect missing opts/args
        if ($this->filename && !is_string($this->filename)) {
            $this->set("filename", $io->prompt("Please enter the filename "));
        }

        $this->table = strtolower($this->filename);
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();

        // more codes ...
        $this->generate();

        if ($this->scaffold)
            return $this->generated;


        !empty($this->generated) && $io->info("All generated files :", true);
        foreach ($this->generated as $file) {
            $io->success($file, true);
        }
        $io->eol();

        // If you return integer from here, that will be taken as exit error code
    }

    private function normalize()
    {
        $this->replacements = array(
            "{table}" => $this->table,
            "{class_name}" => ucfirst($this->filename)
        );
    }

    protected function replace($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                # code...
                $this->replacements[$k] = $v;
            }
        } else {
            $this->replacements[$key] = $value;
        }
        return $this;
    }

    protected function parse_file_content($file)
    {
        $tpl = file_exists($file) ? file_get_contents($file) : "";
        return strtr($tpl, $this->replacements);
    }

    protected function exists($class)
    {
        $stmt = DbConnection::getDb(true)->query("SELECT * FROM " . Schema::quoteIdentifier(Migration::$table) . " WHERE class = '" . $class . "'");
        $migration = null;
        if ($stmt instanceof \PDOStatement) {
            $migration = $stmt->fetchObject();
        }

        if ($migration) {
            $file = $migration->version . '_' . $migration->class . '.php';

            if (file_exists(APP_PATH . "Migration/" . $migration->version . '_' . $migration->class . '.php')) {
                $this->app()->io()->info("A migration file for this class already exists : '$file' And in status of '" . $migration->status . "' !")->eol();
                if ($this->app()->io()->confirm("Do you need to continue with new file ? [y]", "y")) {
                    return $migration;
                }
                $this->app()->io()->info("As of your confirmation, we don't continue. Thanks !")->eol()->eol();
                exit;
            }
        }
        return false;
    }

    public function generate()
    {
        $this->normalize();

        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        if (!is_dir(APP_PATH . "Migration")) {
            @mkdir(APP_PATH . "Migration");
        }

        $migration = $this->exists(ucfirst($this->filename));

        $version = date('ymd_His');
        $filename = $version . '_' . ucfirst($this->filename);

        if (!$this->force && file_exists(APP_PATH . "Migration/" . $filename . '.php')) {
            $this->app()->io()->eol()->warn('"' . end($expl) . "/Migration/" . $filename . '.php" exists, use --force to override !', true)->eol();
            exit(0);
        }
        $DB = DbConnection::getDb(true);

        if (file_put_contents(
            APP_PATH . "Migration/" . $filename . '.php',
            $this->replace([
                "{table}" => $this->table,
                "{class_name}" => ucfirst($this->filename)
            ])->parse_file_content(__DIR__ . "/../Tpl/Migrate.tpl")
        )) {
            if ($migration) {
                $stmt = $DB->prepare("UPDATE " . Schema::quoteIdentifier(Migration::$table) . " SET `version` = ?, `status` = ? WHERE id = ?");

                if (!$stmt->execute([$version, "pending", $migration->id])) {
                    $this->app()->io()->error('Migrations table not updated : "' . $DB->errorInfo()[2] . '" !', true)->eol();
                    exit(0);
                }
                ;
            } else {
                $stmt = $DB->prepare("INSERT INTO " . Schema::quoteIdentifier(Migration::$table) . "(
                            `version`, `class`
                        ) VALUES (? , ?)");

                if (!$stmt->execute([$version, ucfirst($this->filename)])) {
                    $this->app()->io()->error('Migrations table not updated : "' . $DB->errorInfo()[2] . '" !', true)->eol();
                    exit(0);
                }
                ;
            }

            $this->generated[] = end($expl) . "/Migration/" . $filename . '.php';
        }


        return $this->generated;
    }
}