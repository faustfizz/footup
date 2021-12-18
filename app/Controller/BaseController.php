<?php

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/App/Controller
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace App\Controller;
use Footup\Controller;
use App\Libs\Latter;

class BaseController extends Controller
{
    /**
     * Ajouter les middles (middleware) globaux ici pour qu'elles soient utilisées partout dans les controlleurs
     * qui héritent cette classe
     * 
     * @var array [ \Footup\Routing\Middle ] or [ controllerMethod => \Footup\Routing\Middle ]
     * @example -
     * protected $globalMiddles = [
     *  '\App\Middle\Maintenance'
     * ];
     * 
     * or
     * 
     * protected $globalMiddles = [
     *  'index' =>  '\App\Middle\Maintenance'
     * ];
     */
    protected $globalMiddles = [];
    
    /**
     * Si vous voulez ajouter des middles (middleware) spécifiques pour un controlleur, c'est par ici
     * 
     * @var array [ \App\Controller\Home => \Footup\Routing\Middle ] or [ \App\Controller\Home => 
     *                                                                 [ controllerMethod => \Footup\Routing\Middle ]
     *                                                                  ]
     * @example -
     * protected $globalMiddles = [
     *  '\App\Controller\Home'  =>  '\App\Middle\Maintenance'
     * ];
     * 
     * or
     * 
     * protected $globalMiddles = [
     *  \App\Controller\Home'  =>  [
     *                  'index' =>  '\App\Middle\Maintenance'
     *              ]
     * ];
     */
    protected $middles = [];

    /**
     * Ajouter et initialise toutes les classes que vous voulez initier au démarrage de tout controlleur
     * dans la méthode __boot()
     * 
     * 
     * Controller constructor.
     * @param \Footup\Http\Request $request
     * @param \Footup\Http\Response $response
     * @param \Footup\Http\Session $session
     */
    public function __boot(\Footup\Http\Request $request, \Footup\Http\Response $response = null, \Footup\Http\Session $session = null)
    {
        /**
         * Extends the controller here | Etendre le controlleur ici
         * ========================================================
         * ex: $this->cube = new Cube(?$args)
         */

        /**
         * @todo Don't Edit these last lines | Ne modifier pas ces dernières lignes
         */
        parent::__boot($request, $response, $session);
        return $this;
    }

}