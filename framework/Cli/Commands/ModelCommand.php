<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;
use Footup\Generators\Generators;

class ModelCommand extends Command
{
    public $scaffold = false;
    protected $name_space = "App\\Models";

    public function __construct(App $cli)
    {
        parent::__construct('make:model', 'Generate Model classe', false, $cli);
        
        $this
            ->argument('<classname>', 'The name of the class to generate')
            ->option('-n --namespace', 'The namespace of the model class', null, $this->name_space)
            ->option('-f --force', 'Force override file', null, false)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>'
            );

        $this->alias("model");
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io): void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
      /** @var \Footup\Cli\IO\Interactor $io */
        $io = $this->app()->io();

        try {
          //code...
          $result = Generators::model($this->classname, $this->force, $this->namespace);
   
           if ($this->scaffold && $result) {
               return [$result];
           }
   
           $result && $io->eol()->info("Model File generated at: ", false);
           $io->success(without_base_path($result), true);
           $io->eol();
        } catch (\Throwable $th) {
          //throw $th;
          $io->eol()->error($th->getMessage(), true);
        }

        // If you return integer from here, that will be taken as exit error code
    }
}