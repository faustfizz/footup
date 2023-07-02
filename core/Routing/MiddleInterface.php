<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Routing
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;

use Footup\Http\Request;
use Footup\Http\Response;

interface MiddleInterface
{
    /**
     * Execute the middle --- Function called by the core framework
     *
     * @param Request $request
     * @param Response $response
     * @param callable|\Closure $next
     * 
     * @return Response|string|void -- to continue to the next middle return $next($request, $response),
     * return $response to skip all other middle and return a string or void if you need to stop here
     */
    public function execute(Request $request, Response $response, $next);
}