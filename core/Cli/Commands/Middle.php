<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Middle extends Command
{
    protected $classname;
    protected $name_space = "App\\Middle";

    protected $replacements = array(
            "{name_space}"  =>  null,
            "{class_name}"  =>  null
        );
    protected $generated = [];

    public function __construct(App $cli, $classname = null, $namespace = null)
    {
        $this
			->argument('<classname>', 'The name of the class to generate')
            ->option('-n --namespace', 'The namespace of the middleware class')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>' .
                '<bold>  $0</end> <comment> <classname> -n namespace </end> ## Generate the class with namespace<eol/>' 
            );
            
        $this->inGroup("Generator");

        $this->alias("middle");

        parent::__construct('make:middle', 'Generate Middle classe', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
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
        $this->generate();
        !empty($this->generated) && $io->info("All generated files :", true);
        foreach($this->generated as $file)
        {
            $io->success($file, true);
        }

        // If you return integer from here, that will be taken as exit error code
    }

    private function normalize($classname, $namespace)
    {
        $this->classname = trim($classname, " /");
        $this->name_space = is_string($namespace) ? trim($namespace, " /") : $this->name_space;

        $this->replacements = array(
            "{name_space}"  =>  ucfirst($this->name_space),
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
        $this->normalize($this->classname, $this->namespace);

        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        if($hasSlash !== false)
        {
            $expo = explode("/", $this->classname);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return ucfirst($v); }, $expo));
            
            if(!is_dir(APP_PATH."Middle/".ucfirst($dir)))
            {
                @mkdir(APP_PATH."Middle/".ucfirst($dir), 0777, true);
            }
            
            @file_put_contents(
                APP_PATH."Middle/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php',
                $this->replace([
                    "{name_space}" => $this->name_space. '\\'.strtr($dir, "/" , "\\"),
                    "{class_name}" => ucfirst($file)
                ])->parse_file_content(__DIR__."/../Tpl/Middle.tpl")
            );
            
            $this->generated[] =  end($expl)."/Middle/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php';
        }else{
            @file_put_contents(
                APP_PATH."Middle/".ucfirst($this->classname).'.php',$this->replace([
                    "{name_space}" => $this->name_space,
                    "{class_name}" => ucfirst($this->classname)
                ])->parse_file_content(__DIR__."/../Tpl/Middle.tpl")
            );

            $this->generated[] =  end($expl)."/Middle/".ucfirst($this->classname).'.php';
        }

        $this->generated;
    }
}