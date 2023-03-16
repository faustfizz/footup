<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Model extends Command
{
    protected $classname;
    public $scaffold = false;
    protected $name_space = "App\\Model";

    protected $replacements = array(
            "{name_space}"  =>  null,
            "{class_name}"  =>  null,
            "{return_type}" =>  "self",
            "{table}"       =>  null,
            "{primary_key}" =>  null
        );
    protected $generated = [];

    public function __construct(App $cli, $classname = null, $namespace = null)
    {
        $this
			->argument('<classname>', 'The name of the class to generate')
            ->option('-n --namespace', 'The namespace of the model class')
            ->option('-t --table', 'The table name of the model')
            ->option('-r --returnType', 'The return type of the fetched data of the model')
            ->option('-p --primaryKey', 'The primary key of the model table default fall to  id_table')
            ->option('-f --force', 'Force override file', null, false)
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <classname> </end> ## Generate the class without specifying anything than the name<eol/>' .
                '<bold>  $0</end> <comment> <classname> -t tableName -p idTable -r object</end> ## Generate the class with namespace<eol/>' 
            );
            
        $this->inGroup("Generator");

        $this->alias("model");

        parent::__construct('make:model', 'Generate Model classe', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->namespace && !is_string($this->namespace)) {
            $this->set("namespace", $io->prompt("Please give the namespace "));
        }
        if ($this->table && !is_string($this->table)) {
            $this->set("table", $io->prompt("Please give the table name "));
        }
        if ($this->returnType && !is_string($this->returnType)) {
            $this->set("returnType", $io->choice("You can't add empty returnType, Please choose one : ", ["self", "object", "array"], "self"));
        }
        if ($this->primaryKey && !is_string($this->primaryKey)) {
            $this->set("primaryKey", $io->prompt("Please give the primaryKey "));
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

    private function normalize($classname, $namespace)
    {
        $this->classname = trim($classname, " /");
        $this->name_space = is_string($namespace) ? trim($namespace, " /") : $this->name_space;

        $this->replacements = array(
            "{name_space}"  =>  ucfirst($this->name_space),
            "{class_name}"  =>  ucfirst($this->classname),
            "{return_type}" =>  $this->returnType,
            "{table}"       =>  strtolower($this->table ?? $this->classname),
            "{primary_key}" =>  $this->primaryKey ? $this->primaryKey : 'id_'.strtolower($this->table ?? $this->classname)
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

    public function generate($scaffold = false)
    {
        if(!$this->returnType)
        {
            $this->returnType = "self";
        }

        $this->normalize($this->classname, $this->namespace);

        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));

        if($hasSlash !== false)
        {
            $expo = explode("/", $this->classname);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return ucfirst($v); }, $expo));
            
            if(!is_dir(APP_PATH."Model/".ucfirst($dir)))
            {
                @mkdir(APP_PATH."Model/".ucfirst($dir), 0777, true);
            }

            if(!$this->force && file_exists(APP_PATH."Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php'))
            {
                $this->app()->io()->eol()->warn('"'.end($expl)."/Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php" exists, use --force to override !', true)->eol();
                exit(0);
            }

            @file_put_contents(
                APP_PATH."Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php',
                $this->replace([
                    "{name_space}"  => $this->name_space . '\\' . strtr($dir, "/" , "\\"),
                    "{class_name}"  => ucfirst($file),
                    "{return_type}" => $this->returnType,
                    "{table}"       => strtolower($this->table ?? $file),
                    "{primary_key}" => $this->primaryKey ? $this->primaryKey : 'id_'.strtolower($this->table ?? $this->classname),
                ])->parse_file_content(__DIR__."/../Tpl/Model.tpl")
            );
            
            $this->generated[] =  end($expl)."/Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php';

        }else{
            if(!$this->force && file_exists(APP_PATH."Model/".ucfirst($this->classname).'.php'))
            {
                $this->app()->io()->eol()->warn('"'.end($expl)."/Model/".ucfirst($this->classname).'.php" exists, use --force to override !', true)->eol();
                exit(0);
            }
            
            @file_put_contents(
                APP_PATH."Model/".ucfirst($this->classname).'.php',
                $this->replace([
                    "{name_space}"  => $this->name_space,
                    "{return_type}" => $this->returnType,
                    "{table}"       => strtolower($this->table ?? $this->classname),
                    "{primary_key}" => $this->primaryKey ? $this->primaryKey : 'id_'.strtolower($this->table ?? $this->classname),
                ])->parse_file_content(__DIR__."/../Tpl/Model.tpl")
            );
            
            $this->generated[] =  end($expl)."/Model/".ucfirst($this->classname).'.php';
        }

        return $this->generated;
    }
}