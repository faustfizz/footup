<?php

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Cli
 * @version 0.1
 * @author Andreas Gohr <andi@splitbrain.org>, Faustfizz Yous <youssoufmbae2@gmail.com>
 * @license MIT
 */
namespace Footup\Cli;

class Exception extends \RuntimeException
{
    const E_ANY = -1; // no error code specified
    const E_UNKNOWN_OPT = 1; //Unrecognized option
    const E_OPT_ARG_REQUIRED = 2; //Option requires argument
    const E_OPT_ARG_DENIED = 3; //Option not allowed argument
    const E_OPT_ABIGUOUS = 4; //Option abiguous
    const E_ARG_READ = 5; //Could not read argv

    /**
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        if (!$code) {
            $code = self::E_ANY;
        }
        parent::__construct($message, $code, $previous);
    }
}
