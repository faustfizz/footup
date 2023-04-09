<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Routing
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;

use Footup\Http\Request;
use Footup\Http\Response;

abstract class Middle
{
    /**
     * Une fonction pour le middleware qui renvoie (return) TRUE pour permettre que la requête (request)
     * continue, sinon FALSE ou une reponse (response) pour terminer la requête
     *
     * @param Request $request
     * @param Response $response
     * 
     * @return Response|boolean
     */
    abstract public function execute(Request $request, Response $response);
}