<?php
/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/App/Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

class Autoload extends \Footup\Config\Autoload{

    /**
     * Les classmap sont les fichier qu'on souhaite ajouter sans namespace
     * 
     * ex: /same/dir/file_markdown.php use
     * 
     * $classmap = [
     *         "markdown" => "/same/dir/file_markdown"
     * ]
     *
     * @var array
     */ 
    protected $classmap = [];

    /**
     * Pour le namedspace
     * ex: Dir: ./Nol et namespace: pol
     * 
     * $psr4 = [
     *          "pol"   =>  "./Nol/src/"
     * ]
     *
     * @var array
     */ 
    protected $psr4 = [
        "Latte"     => APP_PATH.'Libs/Latte/'
    ];

    /**
     * NE Modifier Pas | Don't Edit
     */
    public function __construct()
    {
        /**
         * @todo DON'T EDIT | À NE PAS EDITER
         */
        parent::__construct($this->psr4, $this->classmap);
    }

    /**
     * NE Modifier Pas | Don't Edit
     */
    public function register()
    {
        /**
         * @todo DON'T EDIT | À NE PAS EDITER
         */
        parent::register();
    }

}