<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Scaffold extends Command
{
    protected $generated = [];

    public function __construct(App $cli)
    {
        $this
            ->argument('<classname>', 'The name of classes to generate')
            ->option('-n --namespace', 'The namespace of these classes')
            ->option('-t --table', 'The table name if you generate Model class')
            ->option('-p --primaryKey', 'The primary key name if you generate Model class')
            ->option('-r --returnType', 'The return type of the fetched data of the model')
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

        $this->alias("scaffold");

        parent::__construct('make:scaffold', 'Generate Controller, Model, Middle, View and Assets files using the same command', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io): void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
        }
        if ($this->table && !is_string($this->table) && $this->type === "model") {
            $this->set("table", $io->prompt("Please give the table name as you selected the model type "));
        }
        if ($this->returnType && !is_string($this->returnType)) {
            $this->set("returnType", $io->choice("You can't add empty returnType, Please choose one : ", ["self", "object", "array"], "self"));
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

        if (!$this->returnType) {
            $this->returnType = "self";
        }

        $generated = [];
        // more codes ...
        # assets...
        $assetsCommand = new Assets($this->app());
        $assetsCommand->scaffold = true;
        $assetsCommand->set('all', 1);
        $this->classname && $assetsCommand->set("filename", $this->classname);
        $this->force && $assetsCommand->set("force", true);
        $generated = array_merge($generated, $assetsCommand->execute());

        # controller...
        $controllerCommand = new Controller($this->app());
        $controllerCommand->scaffold = true;
        $this->classname && $controllerCommand->set("classname", $this->classname);
        $this->namespace && $controllerCommand->set("namespace", $this->namespace);
        $this->force && $controllerCommand->set("force", true);
        $generated = array_merge($generated, $controllerCommand->execute());

        # middle...
        $middleCommand = new Middle($this->app());
        $middleCommand->scaffold = true;
        $this->classname && $middleCommand->set("classname", $this->classname);
        $this->namespace && $middleCommand->set("namespace", $this->namespace);
        $this->force && $middleCommand->set("force", true);
        $generated = array_merge($generated, $middleCommand->execute());

        # model...
        $modelCommand = new Model($this->app());
        $modelCommand->scaffold = true;
        $this->table && $modelCommand->set("table", $this->table);
        $this->classname && $modelCommand->set("classname", $this->classname);
        $this->namespace && $modelCommand->set("namespace", $this->namespace);
        $this->primaryKey && $modelCommand->set("primaryKey", $this->primaryKey);
        $this->returnType && $modelCommand->set("returnType", $this->returnType);
        $this->force && $modelCommand->set("force", true);
        $generated = array_merge($generated, $modelCommand->execute());

        # migration...
        $migrateCommand = new Migrate($this->app());
        $this->classname && $migrateCommand->set("filename", $this->classname);
        $migrateCommand->scaffold = true;
        $this->force && $migrateCommand->set("force", true);
        $generated = array_merge($generated, $migrateCommand->execute());

        # seeder...
        $seederCommand = new Seeder($this->app());
        $this->classname && $seederCommand->set("classname", $this->classname);
        $seederCommand->scaffold = true;
        $this->force && $seederCommand->set("force", true);
        $generated = array_merge($generated, $seederCommand->execute());

        # view...
        $viewCommand = new View($this->app());
        $viewCommand->scaffold = true;
        $this->extension && $viewCommand->set("ext", $this->extension);
        $this->force && $viewCommand->set("force", true);
        $this->classname && $viewCommand->set("filename", $this->classname);
        $generated = array_merge($generated, $viewCommand->execute());
        // If you return integer from here, that will be taken as exit error code

        !empty($generated) && $io->info("All generated files :", true);
        foreach ($generated as $file) {
            $io->success($file, true);
        }
        $io->eol();
    }
}