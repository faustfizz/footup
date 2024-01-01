<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;
use Footup\Database\DbConnection;
use Footup\Database\Migration;
use Footup\Database\Schema\Schema;

class Migscan extends Command
{
    use MigrateTrait;
    public $scaffold = false;
    protected $table;
    protected $namespace = "App\\Migration";
    protected $generated = [];

    public function __construct(App $cli)
    {
        $this->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> ## Scan the migration directory and import migrations classes into the migrations table<eol/>'
            );

        $this->inGroup("Migration");

        $this->alias("mg-scan");

        parent::__construct('migrate:scan', 'Scan the migration directory and add all migrations classes into the migrations table', false, $cli);

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
            $io->warn($th->getMessage() . ". It means you cannot run migrations commands". $th->getLine())->eol();
            exit;
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();

        // more codes ...
        $this->generate();

        !empty($this->generated) && $io->info("All imported files :", true);
        foreach ($this->generated as $file) {
            $io->success($file, true);
        }
        $io->eol();

        // If you return integer from here, that will be taken as exit error code
    }

    protected function scan()
    {
        try {
            $DB = DbConnection::getDb(true);
            $migrations = $this->getMigrations();

            $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

            $versions = empty($migrations) ? [] : array_map(function ($mig) {
                return $mig->version; }, $migrations);
                
            foreach (glob(APP_PATH . 'Migration/*.php') as $migFile) {
                # code...
                $explodeFile = explode('/', $migFile);
                $migFile = end($explodeFile);
                preg_match('/(\d+_\d+)_(\w+_?\w+)\.php/i', $migFile, $matches);
                
                list($file, $version, $class) = $matches;

                if (in_array($version, $versions)) {
                    continue;
                }

                $stmt = $DB->prepare("INSERT INTO " . Schema::quoteIdentifier(Migration::$table) . "(
                        `version`, `class`
                    ) VALUES (? , ?)");

                if (!$stmt->execute([$version, ucfirst($class)])) {
                    $this->app()->io()->error('Migrations table not updated : "' . $DB->errorInfo()[2] . '" !', true)->eol();
                    exit(0);
                }

                $this->generated[] = end($expl) . "/Migration/$file";
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->app()->io()->warn($th->getMessage() . ". It means you cannot run migrations commands". $th->getLine())->eol();
            return false;
        }
    }

    public function generate()
    {
        try {
            $this->scan();
        } catch (\Throwable $th) {
            //throw $th;
            $this->app()->io()->warn($th->getMessage() . ". It means you cannot run migrations commands". $th->getLine())->eol();
        }

        return $this->generated;
    }
}