<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Debug
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 *
 * 
 * Primarly was:
 * Ouch error handler for PHP.
 *
 * @author      Lotfio Lakehal <lotfiolakehal@gmail.com>
 * @copyright   2018 Lotfio Lakehal
 * @license     MIT
 *
 * @link        https://github.com/lotfio/ouch
 */

namespace Footup\Debug;

include_once(__DIR__."/Support/functions.php");

class Reporter
{
    /**
     * @var HandlersSetter
     */
    private $handler;

    /**
     * Recorder constructor.
     */
    public function __construct()
    {
        ob_start(); // prevent html before error
        ini_set('display_errors', '0'); // prevent error duplication on fatal & cli
        error_reporting(0);
        $this->handler = new HandlersSetter(new Handlers());
    }

    /**
     * enable ouch error handler.
     *
     * @param string $env prod | dev
     *
     * @return self
     */
    public function enableErrorHandler(string $env = 'prod'): self
    {
        $this->handler->setEnvirenment($env);
        $this->handler->setErrorHandler();
        $this->handler->setExceptionHandler();
        $this->handler->setFatalHandler();

        return $this;
    }

    /**
     * disable ouch error handler
     * and restore default error handler.
     *
     * @return void
     */
    public function disableErrorHandler(): void
    {
        $this->handler->restoreErrorHandler();
        $this->handler->restoreExceptionHandler();
    }
}
