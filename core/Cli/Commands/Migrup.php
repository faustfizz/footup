<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;
use Footup\Database\DbConnection;
use Footup\Database\Schema\Schema;

class Migrup extends Command
{
    use MigrateTrait;
    public $scaffold = false;
    protected $namespace = "\\App\\Migration\\";
    protected $generated = [];
    protected Schema $schema;

    public function __construct(App $cli)
    {
        $this
            ->argument('[classname]', 'The name of the migration class to run up')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> [classname] </end> ## run classname, if not set, all migratins will run !<eol/>'
            );

        $this->inGroup("Migration");

        $this->alias("migrate");

        $this->schema = new Schema();

        parent::__construct('migrate:up', 'Run up a migration or all migrations', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io): void
    {
        try {
            //code...
            $this->schema = new Schema(DbConnection::getDb(true));
        } catch (\Throwable $th) {
            //throw $th;
            $io->warn($th->getMessage() . ". It means you cannot run migrations commands")->eol();
            exit;
        }

        if ($this->classname && !is_string($this->classname)) {
            $io->warn("No name provided, if you don't give one, We will up all tables created with migrations.")->eol();
            if ($io->confirm("Do you agree to you give one ?")) {
                $this->set("classname", $io->prompt("Ok ! give the name please : "));
            }
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();

        // more codes ...
        $this->runMigration("up");

        if ($this->scaffold)
            return $this->generated;


        !empty($this->generated) && $io->info("All operation's results :", true);
        foreach ($this->generated as $file) {
            $io->success($file, true);
        }
        $io->eol();

        // If you return integer from here, that will be taken as exit error code
    }
}