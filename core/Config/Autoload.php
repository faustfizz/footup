<?php

/**
 * FOOTUP - 0.1.6 - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Config
 * @version 0.4
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Config;

class Autoload{

    protected $classmap = [];
    protected $psr4 = [
        "Footup"    =>  SYS_PATH,
        "App"       =>  APP_PATH,
    ];

    public function __construct()
    {
        $this->register();
    }

    /**
     * @return self
     */
    public function register()
    {
        $this->copyComposerNamespaces();

        spl_autoload_register([$this, 'mount'], true, true);

        spl_autoload_register(function ($class) {
			if (empty($this->classmap[$class]))
			{
				return false;
			}
            if(file_exists($this->classmap[$class]))
            {
                include_once $this->classmap[$class];
            }
		}, true, true);

        /**
         * @todo DON'T EDIT | NE TOUCHER JAMAIS CETTE LIGNE
         */
        $this->psr4 = array_merge($this->psr4, \App\Config\Autoload::$psr4);
        $this->classmap = array_merge($this->classmap, \App\Config\Autoload::$classmap);

        return $this;
    }

    /**
     * Auto chargement d'une classe
     *
     * @param string $class
     * @return void
     */
    public function mount($class)
    {
        foreach ($this->psr4 as $namespace => $match) {
            # code...
            $file = strtr($class, [rtrim($namespace, "\\")."\\" => rtrim($match, DS).DS, "\\" => DS]);
    
            if(file_exists($file. ".php"))
            {
                return require_once($file.'.php');
            }
        }
    }
	//--------------------------------------------------------------------

	/**
	 * Locates all PSR4 & classMap compatible namespaces from Composer.
	 */
	protected function copyComposerNamespaces()
	{
        $composer_autoloader = ROOT_PATH."vendor/autoload.php";

		if (! is_file($composer_autoloader))
		{
			return false;
		}

        /**
         * @var \Composer\Autoload\ClassLoader
         */
		$composer = include $composer_autoloader;

		$paths = $composer->getPrefixesPsr4();
        $classmaps = $composer->getClassMap();

        foreach ($classmaps as $key => $value) {
            # code...
            if(is_string($key) && stripos($key, "composer"))
            {
                unset($classmaps[$key]);
            }
        }

		$this->classmap = array_merge($this->classmap, $classmaps);

		unset($composer);

		// Get rid of CodeIgniter so we don't have duplicates
		if (isset($paths['Footup\\']))
		{
			unset($paths['Footup\\']);
		}

		// Composer stores namespaces with trailing slash. We don't.
		$newPaths = [];
		foreach ($paths as $key => $value)
		{
			$newPaths[rtrim($key, '\\ ')] = $value;
		}

		$this->psr4 = array_merge($this->psr4, $newPaths);
	}

}