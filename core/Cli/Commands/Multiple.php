<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Multiple extends Command
{
    protected $generated = [];

    public function __construct(App $cli)
    {
        $this
			->argument('<classname> [...]', 'The name of classes to generate')
            ->option('-n --namespace', 'The namespace of these classes')
            ->option('-T --type', 'The type can be controller, model, middle, migration, seeder or view class')
            ->option('-t --table', 'The table name if you generate Model class')
            ->option('-r --returnType', 'The return type of the fetched data of the model')
            ->option('-p --primaryKey', 'The primary key name if you generate Model class')
            ->option('-x --extension', 'The extension of the view file')
            ->option('-f --force', 'Force override file', null, false)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>' .
                '<bold>  $0</end> <comment> <classname> -n namespace </end> ## Generate the class with namespace<eol/>' .
                '<comment> ## All options not matching the selected type are ignored<eol/>'
            );
            
        $this->inGroup("Helper");

        $this->alias("multiple");

        parent::__construct('make:multiple', 'Generate Multiple classes of same type (Controller | Model | Middle | View)', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
        }
        if ($this->type && !is_string($this->type)) {
            $this->set("type", $io->choice("Choose the type of file [controller, model, middle, migration, seeder, view]; default: controller ", ["controller", "model", "middle", "migration", "seeder", "view"], "controller"));
        }
        if ($this->returnType && !is_string($this->returnType)) {
            $this->set("returnType", $io->choice("You can't add empty returnType, Please choose one : ", ["self", "object", "array"], "self"));
        }
        if ($this->table && !is_string($this->table) && $this->type === "model") {
            $this->set("table", $io->prompt("Please give the table name as you selected the model type "));
        }
        if ($this->primaryKey && !is_string($this->primaryKey) && $this->type === "model") {
            $this->set("primaryKey", $io->prompt("Please give the primaryKey as you selected the model type "));
        }
        // ...
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();
        
        if(!$this->returnType)
        {
            $this->returnType = "self";
        }

        $classnames = array_unique($this->values(0)["classname"]);
        // more codes ...
        foreach ($classnames as $value) {
            switch($this->type)
            {
                case "controller":
                case "middle":
                    # code...
                    $contrMiddleCommand = $this->type === "middle" ? new Middle($this->app()) : new Controller($this->app());
                    $this->force && $contrMiddleCommand->set("force", true);
                    $value && $contrMiddleCommand->set("classname", $value);
                    $this->namespace && $contrMiddleCommand->set("namespace", $this->namespace);
                    $contrMiddleCommand->execute();
                    break;
                case "model":
                    # code...
                    $modelCommand = new Model($this->app());
                    $this->table && $modelCommand->set("table", $this->table);
                    $value && $modelCommand->set("classname", $value);
                    $this->namespace && $modelCommand->set("namespace", $this->namespace);
                    $this->primaryKey && $modelCommand->set("primaryKey", $this->primaryKey);
                    $this->returnType && $modelCommand->set("returnType", $this->returnType);
                    $this->force && $modelCommand->set("force", true);
                    $modelCommand->execute();
                    break;
                case "view":
                    # code...
                    $viewCommand = new View($this->app());
                    $this->extension && $viewCommand->set("ext", $this->extension);
                    $value && $viewCommand->set("filename", $value);
                    $this->force && $viewCommand->set("force", true);
                    $viewCommand->execute();
                    break;
                case "migration":
                    # code...
                    $migrateCommand = new Migrate($this->app());
                    $value && $migrateCommand->set("filename", $value);
                    $this->force && $migrateCommand->set("force", true);
                    $migrateCommand->execute();
                    break;
                case "seeder":
                    # code...
                    $seederCommand = new Seeder($this->app());
                    $value && $seederCommand->set("classname", $value);
                    $this->force && $seederCommand->set("force", true);
                    $seederCommand->execute();
                break;
            }
        }

        // If you return integer from here, that will be taken as exit error code
    }
}