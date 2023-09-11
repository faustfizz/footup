<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;
use Footup\Database\DbConnection;
use Footup\Database\Schema\Schema;

class Seed extends Command
{
    public $scaffold = false;
    protected $namespace = "\\App\\Seed\\";
    protected $generated = [];
    protected Schema $schema;

    public function __construct(App $cli)
    {
        $this
            ->argument('[classname]', 'The name of the seed classname to run up')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> [classname] </end> ## run classname, if not set, all seeds will run !<eol/>'
            );

        $this->inGroup("Seeder");

        $this->alias("breed");

        parent::__construct('egg:breed', 'Run a seed class or all seeds', false, $cli);
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
                $this->set("classname", $io->prompt("Ok ! give the name please : ", ""));
            }
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();

        // more codes ...
        $this->runSeed();

        if ($this->scaffold)
            return $this->generated;


        !empty($this->generated) && $io->info("All breeded classes :", true);
        foreach ($this->generated as $file) {
            $io->success($file, true);
        }
        $io->eol();

        // If you return integer from here, that will be taken as exit error code
    }

    /**
     * Run Seed
     */
    protected function runSeed()
    {
        $io = $this->app()->io();

        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        $totalSuccess = 0;

        if ($this->classname) {
            $filename = APP_PATH . "Seed/" . ucfirst($this->classname) . ".php";
            if (file_exists($filename)) {
                require_once($filename);
                /**
                 * @var string
                 */
                $className = $this->namespace . ucfirst($this->classname);

                /**
                 * @var \Footup\Database\Seeder
                 */
                $seeder = new $className();
                $seeder->run();

                $this->generated[] = end($expl) . "/Seed/" . ucfirst($this->classname);
            }
        } else {
            // You want to run all seeder
            foreach (glob(APP_PATH . "Seed/*.php") as $seedFile) {
                require_once($seedFile);
                $filename = basename($seedFile, ".php");

                /**
                 * @var string
                 */
                $className = $this->namespace . ucfirst($filename);

                /**
                 * @var \Footup\Database\Seeder
                 */
                $seeder = new $className();
                $seeder->run();

                $this->generated[] = end($expl) . "/Seed/" . ucfirst($filename);
                $totalSuccess += 1;
                unset($seeder);
            }

            if ($totalSuccess) {
                array_unshift($this->generated, "Total $totalSuccess Breeded successfully !");
            }
        }
        return $this->generated;
    }

}