<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class View extends Command
{
    protected $filename;
    protected $extension = "php";

    protected $replacements = array(
            "{classname}"   =>  null,
            "{_classname}"  =>  null
        );
    protected $generated = [];

    public function __construct(App $cli, $filename = null, $extension = "php")
    {
        $this
			->argument('<filename>', 'The name of the file to generate')
            ->option('-x --ext', 'The extension of the view file')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <filename> </end> ## Generate the file without specifying anything than the name<eol/>' .
                '<bold>  $0</end> <comment> <filename> -x html </end> ## Generate the file without specifying anything than the name<eol/>' 
            );
            
        $this->inGroup("Generator");

        $this->alias("view");

        parent::__construct('make:view', 'Generate View file', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if($this->ext && !is_string($this->ext))
        {
            $this->set("ext", $io->promt("You typed the -x option so please give it a value "));
        }
        
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($filename)
    {
        $io = $this->app()->io();
        $this->extension = $this->ext ? $this->ext : $this->extension;
        
        if($filename)
        {
            $this->filename = $filename;
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

    private function normalize($filename, $extension)
    {
        $this->filename = $filename ? trim($filename, " /") : $this->filename;
        $this->extension = is_string($extension) ? trim($extension, " /") : $this->extension;

        $this->replacements = array(
            "{classname}"   =>  strtolower($this->extension),
            "{_classname}"  =>  strtolower($this->filename)
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
        $this->normalize($this->filename, $this->extension);
        
        $hasSlash = strpos($this->filename, "/");
        $expl = explode("/", trim(APP_PATH, DIRECTORY_SEPARATOR));
        
        if($hasSlash !== false)
        {
            $expo = explode("/", $this->filename);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return strtolower($v); }, $expo));
            
            if(!is_dir(VIEW_PATH."/".strtolower($dir)))
            {
                @mkdir(VIEW_PATH."/".strtolower($dir), 0777, true);
            }
            
            @file_put_contents(
                VIEW_PATH."/".strtolower($dir).DIRECTORY_SEPARATOR.strtolower($file).'.' . $this->extension,
                $this->replace([
                    "{class_name}" => strtolower($file),
                    "{_class_name}" => strtolower($file)
                ])->parse_file_content(__DIR__."/../Tpl/View.tpl")
            );
            
            $this->generated[] = end($expl)."/View/".strtolower($dir).DIRECTORY_SEPARATOR.strtolower($file).'.' . $this->extension;
        }else{
            @file_put_contents(
                VIEW_PATH."/".strtolower($this->filename).'.' . $this->extension,
                $this->replace([
                    "{class_name}" => strtolower($this->filename),
                    "{_class_name}" => strtolower($this->filename)
                ])->parse_file_content(__DIR__."/../Tpl/View.tpl")
            );
            
            $this->generated[] = end($expl)."/View/".strtolower($this->filename).'.' . $this->extension;
        }

        $this->generated;
    }
}