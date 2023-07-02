<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\App\Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

class Autoload
{

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
    public static $classmap = [];

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
    public static $psr4 = [ ];

}