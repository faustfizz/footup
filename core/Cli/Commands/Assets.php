<?php

namespace Footup\Cli\Commands;

use Footup\Cli\Input\Command;
use Footup\Cli\IO\Interactor;
use Footup\Cli\Konsole as App;

class Assets extends Command
{
    protected $filename;
    protected $generated = [];

    public function __construct(App $cli, $filename = null)
    {
        $this->filename = $filename;

        $this
			->argument('<filename>', 'The name without extension of the js and css files')
            ->option('-a --all', 'Generate CSS and JS', null, true)
            ->option('-c --css', 'Generate just the css file')
            ->option('-j --js', 'Generate just the js file')
            // Usage examples:
            ->usage(
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment> <filename> -a </end> ## Generate CSS and JS<eol/>' .
                '<bold>  $0</end> <comment> <filename> -c </end> ## Generate just the CSS file<eol/>' .
                '<bold>  $0</end> <comment> <filename> -j </end> ## Generate just the JS<eol/>'
            );
            
        $this->inGroup("Generator");

        $this->alias("assets");

        parent::__construct('make:assets', 'Generate Assets files (CSS and JS)', false, $cli);
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io) :void
    {
        // Collect missing opts/args
        if ($this->css || $this->js) {
            $this->unset("all");
        }
        // ...
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($filename)
    {
        $io = $this->app()->io();
        
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



    public function generate()
    {
        $expl = explode("/", trim(ASSETS_DIR, DIRECTORY_SEPARATOR));
        $class = strpos($this->filename, "/") !== false ? explode("/", strtolower($this->filename)) : [strtolower($this->filename)];
        
        if(!is_dir(ASSETS_DIR."css/"))
        {
            @mkdir(ASSETS_DIR."css/", 0777, true);
        }
        if(!is_dir(ASSETS_DIR."js/"))
        {
            @mkdir(ASSETS_DIR."js/", 0777, true);
        }
        if($this->css || $this->all)
        {
            if(file_put_contents(ASSETS_DIR."css/".strtolower(end($class)).'.css', "/* Put CSS Code here */"))
            {
                $this->generated[] = end($expl)."/css/".strtolower(end($class)).'.css';
            }
        }
        if($this->js || $this->all)
        {
            if(file_put_contents(ASSETS_DIR."js/".strtolower(end($class)).'.js', "/* Put JS Code here */"))
            {
                $this->generated[] = end($expl)."/js/".strtolower(end($class)).'.js';
            }
        }
        
        return $this->generated;
    }
}