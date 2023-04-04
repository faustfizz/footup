<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Seeder extends Command
{
    public $scaffold = false;

    protected $replacements = array(
            "{class_name}"  =>  null
        );
    protected $generated = [];

    public function __construct(App $cli)
    {
        $this
			->argument('<classname>', 'The name of the seeder class to generate')
            ->option('-f --force', 'Force override file', null, false)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>' 
            );
            
        $this->inGroup("Seeder");

        $this->alias("plant");

        parent::__construct('egg:plant', 'Generate seeder file', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->classname && !is_string($this->classname)) {
            $this->set("classname", $io->prompt("Please enter the class's name "));
        }
        // ...
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute()
    {
        $io = $this->app()->io();

        // more codes ...
        $this->generate();

        if($this->scaffold)
            return $this->generated;

        
        !empty($this->generated) && $io->info("All generated files :", true);
        foreach($this->generated as $file)
        {
            $io->success($file, true);
        }
        $io->eol();

        // If you return integer from here, that will be taken as exit error code
    }

    private function normalize()
    {
        $this->replacements = array(
            "{table}"  =>  strtolower($this->classname),
            "{class_name}"  =>  ucfirst($this->classname)
        );
    }

    protected function replace($key, $value = null)
    {
        if(is_array($key))
        {
            foreach ($key as $k => $v) {
                # code...
                $this->replacements[$k] = $v;
            }
        }else{
            $this->replacements[$key] = $value;
        }
        return $this;
    }

    protected function parse_file_content($file)
    {   
        $tpl = file_exists($file) ? file_get_contents($file) : "";
        return strtr($tpl, $this->replacements);
    }

    public function generate()
    {
        $this->normalize();
        
        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        if(!is_dir(APP_PATH."Seed")){
            @mkdir(APP_PATH."Seed");
        }

        $filename = ucfirst($this->classname);
        
        if(!$this->force && file_exists(APP_PATH."Seed/".$filename.'.php'))
        {
            $this->app()->io()->eol()->warn('"'.end($expl)."/Seed/".$filename.'.php" exists, use --force to override !', true)->eol();
            exit(0);
        }
        
        if(file_put_contents(
            APP_PATH."Seed/".$filename.'.php',
            $this->replace([
                "{table}" => strtolower($this->classname),
                "{class_name}" => ucfirst($this->classname)
            ])->parse_file_content(__DIR__."/../Tpl/Seed.tpl")
        )){
            $this->generated[] =  end($expl)."/Seed/".$filename.'.php';
        }

        return $this->generated;
    }
}