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

use Footup\Debug\Contracts\HandlersInterface;

class HandlersSetter
{
    /**
     * @var HandlersInterface
     */
    private $handlers;

    /**
     * HandlersSetter constructor.
     *
     * @param HandlersInterface $handlers
     */
    public function __construct(HandlersInterface $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * set envirenment.
     *
     * @param string $env pro | dev
     *
     * @return string
     */
    public function setEnvirenment(string $env): string
    {
        return $this->handlers->env = $env;
    }

    /**
     * set error Handler.
     *
     * @return void
     */
    public function setErrorHandler(): void
    {
        set_error_handler([$this->handlers, 'errorHandler']);
    }

    /**
     * set exception handler.
     *
     * @return void
     */
    public function setExceptionHandler(): void
    {
        set_exception_handler([$this->handlers, 'exceptionHandler']);
    }

    /**
     * set fatal handler.
     *
     * @return void
     */
    public function setFatalHandler(): void
    {
        register_shutdown_function([$this->handlers, 'fatalHandler']);
    }

    /**
     * restore error handler.
     *
     * @return void
     */
    public function restoreErrorHandler(): void
    {
        restore_error_handler();
    }

    /**
     * restore exception handler.
     *
     * @return void
     */
    public function restoreExceptionHandler(): void
    {
        restore_exception_handler();
    }
}
