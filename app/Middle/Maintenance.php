<?php

/**
 * Middleware Exemple
 */

namespace App\Middle;

use Footup\Http\Request;
use Footup\Http\Response;

use Footup\Routing\Middle;

class Maintenance extends Middle
{
    public function execute(Request $request, Response $response)
    {
        return $response->die(503, "Site Web en Maintenance" ,"Ce site est en maintenance !");
    }
}