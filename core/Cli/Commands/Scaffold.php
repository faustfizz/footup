<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Scaffold extends Command
{
    protected $classname;
    protected $generated = [];

    public function __construct(App $cli, $classname = null, $namespace = null)
    {
        $this
			->argument('<classname>', 'The name of classes to generate')
            ->option('-n --namespace', 'The namespace of these classes')
            ->option('-t --table', 'The table name if you generate Model class')
            ->option('-p --primaryKey', 'The primary key name if you generate Model class')
            ->option('-x --extension', 'The extension of the view file')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>' .
                '<bold>  $0</end> <comment> <classname> -n namespace </end> ## Generate the class with namespace<eol/>' .
                '<comment> ## All options not matching the selected type are ignored<eol/>'
            );
            
        $this->inGroup("Helper");

        $this->alias("scaffold");

        parent::__construct('make:scaffold', 'Scaffold classes of same type (Controller | Model | Middle | View)', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
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
    public function execute($classname)
    {
        $io = $this->app()->io();
        
        if($classname)
        {
            $this->classname = $classname;
        }

        // more codes ...
        # assets...
        $assetsCommand = new Assets($this->app(), $this->classname);
        $assetsCommand->set('all', 1);
        $assetsCommand->execute($this->classname);

        # middle...
        $middleCommand = new Middle($this->app(), $this->classname, $this->namespace);
        $middleCommand->execute($this->classname);

        # controller...
        $controllerCommand = new Controller($this->app(), $this->classname, $this->namespace);
        $controllerCommand->execute($this->classname);

        # model...
        $modelCommand = new Model($this->app(), $this->classname, $this->namespace);
        $this->table && $modelCommand->set("table", $this->table);
        $this->primaryKey && $modelCommand->set("primaryKey", $this->primaryKey);
        $modelCommand->execute($this->classname);
        
        # view...
        $viewCommand = new View($this->app(), $this->classname);
        $this->extension && $viewCommand->set("ext", $this->extension);
        $viewCommand->execute($this->classname);
        // If you return integer from here, that will be taken as exit error code
    }
}