<?php

/**
 * FOOTUP - 0.1.4 - 12.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Cli
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 * @license MIT
 */
namespace Footup\Cli;

class Generator
{
    protected $namespace = "";
    protected $classname = "";
    protected $replacements = array();

    public function __construct(string $class_name, $namespace = "")
    {
        $this->classname = trim($class_name, " \t\n\r\0\x0B/");
        $this->namespace = trim($namespace, " \t\n\r\0\x0B/");
        $this->replacements = array(
            "{name_space}"  =>  ucfirst($this->namespace),
            "{class_name}"  =>  ucfirst($this->classname),
            "{class_view}"  =>  strtolower($this->classname),
            "{append_table}"  =>  "_".strtolower($this->classname),
            "{use_header}"  =>  "",
        );
    }

    protected function genReplace($key, $value = null)
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

    public function genController($scaffold = false)
    {
        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(strtr(APP_PATH, ["/Config/.." => ""]), DIRECTORY_SEPARATOR));
        
        if($hasSlash !== false)
        {
            $expo = explode("/", $this->classname);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return ucfirst($v); }, $expo));
            
            if(!is_dir(APP_PATH."Controller/".ucfirst($dir)))
            {
                @mkdir(APP_PATH."Controller/".ucfirst($dir), 0777, true);
            }
            
            @file_put_contents(
                APP_PATH."Controller/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php',
                $this->genReplace([
                    "{name_space}" => '\\'.strtr($dir, "/" , "\\"),
                    "{class_name}" => ucfirst($file),
                    "{class_view}" => $dir.DIRECTORY_SEPARATOR.strtolower($file),
                    "{use_header}" => $scaffold ? "use App\Controller\BaseController;\nuse App\Model\\".strtr($dir, "/" , "\\")."\\".ucfirst($file)." as ".ucfirst($file)."Model;\n" : "use App\Controller\BaseController;\n"
                ])->parse_file_content(__DIR__."/Tpl/Controller.tpl")
            );
            
            return [
                end($expl)."/Controller/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php'
            ];
        }else{
            @file_put_contents(
                APP_PATH."Controller/".ucfirst($this->classname).'.php',
                $this->genReplace([
                    "{use_header}" => $scaffold ? "use App\Model\\".ucfirst($this->classname)." as ".ucfirst($this->classname)."Model;\n" : ""
                ])->parse_file_content(__DIR__."/Tpl/Controller.tpl")
            );
            
            return [
                end($expl)."/Controller/".ucfirst($this->classname).'.php'
            ];
        }
    }

    public function genAssets()
    {
        $expl = explode("/", trim(ASSETS_DIR, DIRECTORY_SEPARATOR));
        $class = strpos($this->classname, "/") !== false ? explode("/", strtolower($this->classname)) : [strtolower($this->classname)];
        if(!is_dir(ASSETS_DIR."css/"))
        {
            @mkdir(ASSETS_DIR."css/", 0777, true);
        }
        if(!is_dir(ASSETS_DIR."js/"))
        {
            @mkdir(ASSETS_DIR."js/", 0777, true);
        }
        @file_put_contents(ASSETS_DIR."css/".strtolower(end($class)).'.css', "/* Put CSS Code here */");
        @file_put_contents(ASSETS_DIR."js/".strtolower(end($class)).'.js', "/* Put JS Code here */");
        return [
            end($expl)."/css/".strtolower(end($class)).'.css',
            end($expl)."/js/".strtolower(end($class)).'.js'
        ];
    }

    public function genModel()
    {
        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(strtr(APP_PATH, ["/Config/.." => ""]), DIRECTORY_SEPARATOR));
        
        if($hasSlash !== false)
        {
            $expo = explode("/", $this->classname);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return ucfirst($v); }, $expo));
            
            if(!is_dir(APP_PATH."Model/".ucfirst($dir)))
            {
                @mkdir(APP_PATH."Model/".ucfirst($dir), 0777, true);
            }
            
            @file_put_contents(
                APP_PATH."Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php',
                $this->genReplace([
                    "{name_space}" => '\\'.strtr($dir, "/" , "\\"),
                    "{class_name}" => ucfirst($file),
                    "{table}" => strtolower($file),
                ])->parse_file_content(__DIR__."/Tpl/Model.tpl")
            );
            
            return [
                end($expl)."/Model/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php'
            ];
        }else{
            @file_put_contents(
                APP_PATH."Model/".ucfirst($this->classname).'.php',
                $this->genReplace([
                    "{table}" => strtolower($this->classname),
                ])->parse_file_content(__DIR__."/Tpl/Model.tpl")
            );
            
            return [
                end($expl)."/Model/".ucfirst($this->classname).'.php'
            ];
        }
    }

    public function genView()
    {
        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(strtr(VIEW_PATH, ["/Config/.." => ""]), DIRECTORY_SEPARATOR));
        
        if($hasSlash !== false)
        {
            $expo = explode("/", $this->classname);
            $file = array_pop($expo);
            $dir = implode("/", array_map(function($v){ return strtolower($v); }, $expo));
            
            if(!is_dir(VIEW_PATH."/".strtolower($dir)))
            {
                @mkdir(VIEW_PATH."/".strtolower($dir), 0777, true);
            }
            
            @file_put_contents(
                VIEW_PATH."/".strtolower($dir).DIRECTORY_SEPARATOR.strtolower($file).'.php',
                $this->genReplace([
                    "{name_space}" => '\\'.strtr($dir, "/" , "\\"),
                    "{class_name}" => strtolower($file),
                    "{_class_name}" => strtolower($file)
                ])->parse_file_content(__DIR__."/Tpl/View.tpl")
            );
            
            return [
                end($expl)."/".strtolower($dir).DIRECTORY_SEPARATOR.strtolower($file).'.php'
            ];
        }else{
            @file_put_contents(
                VIEW_PATH."/".strtolower($this->classname).'.php',
                $this->genReplace([
                    "{_class_name}" => strtolower($this->classname)
                ])->parse_file_content(__DIR__."/Tpl/View.tpl")
            );
            
            return [
                end($expl)."/".strtolower($this->classname).'.php'
            ];
        }
    }

    public function genMiddle()
    {
        $hasSlash = strpos($this->classname, "/");
        $expl = explode("/", trim(strtr(APP_PATH, ["/Config/.." => ""]), DIRECTORY_SEPARATOR));

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
                $this->genReplace([
                    "{name_space}" => '\\'.strtr($dir, "/" , "\\"),
                    "{class_name}" => ucfirst($file)
                ])->parse_file_content(__DIR__."/Tpl/Middle.tpl")
            );
            
            return [
                end($expl)."/Middle/".ucfirst($dir).DIRECTORY_SEPARATOR.ucfirst($file).'.php'
            ];
        }else{
            @file_put_contents(
                APP_PATH."Middle/".ucfirst($this->classname).'.php',
                $this->parse_file_content(__DIR__."/Tpl/Middle.tpl")
            );

            return [
                end($expl)."/Middle/".ucfirst($this->classname).'.php'
            ];
        }
    }

    public function scaffold()
    {
        $cont = $this->genController(true);
        $mod = $this->genModel();
        $mid = $this->genMiddle();
        $view = $this->genView();
        $ass = $this->genAssets();

        return array_merge($cont, $mod, $mid, $view, $ass);
    }

}
