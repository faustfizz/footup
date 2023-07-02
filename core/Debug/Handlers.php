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

use ErrorException;
use Footup\Debug\Contracts\HandlersInterface;

class Handlers implements HandlersInterface
{
    /**
     * envirenment variable.
     *
     * @var string
     */
    public $env;
    /**
     * errors repo.
     *
     * @var array
     */
    private $errors = [];

    /**
     * @param int    $type
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return void
     */
    public function errorHandler(int $type, string $message, string $file, int $line): void
    {
        $this->whichError($message, $type, $file, $line);
    }

    /**
     * exception handler.
     *
     * @param object $e exception object
     *
     * @return void throw exception based on the error type
     */
    public function exceptionHandler($e): void
    {
        $this->setError(
            (int) $e->getCode(),
            (string) $e->getMessage(),
            (string) $e->getFile(),
            (int) $e->getLine(),
            (string) get_class($e),
            $e->getTrace()
        );

        die(
            View::render('500.php', (object) $this->errors, $this->env)
        );
    }

    /**
     * error handler method.
     *
     * @return void
     */
    public function fatalHandler(): void
    {
        $errors = error_get_last();
        if (is_array($errors)) {
            $this->setError(
                (int) $errors['type'],
                (string) $errors['message'],
                (string) $errors['file'],
                (int) $errors['line'],
                'FatalErrorException'
            );

            die(
                View::render('500.php', (object) $this->errors, $this->env)
            );
        }
    }

    /**
     * @param int    $type
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param string $class
     * @param array  $trace
     *
     * @return array
     */
    public function setError(int $type, string $message, string $file, int $line, string $class, array $trace = []): array
    {
        return $this->errors = [
            'type'    => $type,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'class'   => $class,
            'trace'   => $trace,
        ];
    }

    /**
     * @param string $message
     * @param int    $type
     * @param string $file
     * @param int    $line
     *
     * @throws ErrorException
     * @throws Exceptions\WarningException
     * @throws Exceptions\ParseErrorException
     * @throws Exceptions\NoticeException
     * @throws Exceptions\CoreErrorException
     * @throws Exceptions\CompileErrorException
     * @throws Exceptions\CompileWarningException
     * @throws Exceptions\UserErrorException
     * @throws Exceptions\UserWarningException
     * @throws Exceptions\UserNoticeException
     * @throws Exceptions\StrictErrorException
     * @throws Exceptions\RecoverableErrorException
     * @throws Exceptions\DepricatedException
     * @throws Exceptions\UserDeprecatedException
     * @throws ErrorException
     */
    public function whichError(string $message, int $type, string $file, int $line): void
    {
        switch ($type) {
            case E_ERROR: throw new ErrorException($message, $type, $type, $file, $line);
            case E_WARNING: throw new Exceptions\WarningException($message, $type, $type, $file, $line);
            case E_PARSE: throw new Exceptions\ParseErrorException($message, $type, $type, $file, $line);
            case E_NOTICE: throw new Exceptions\NoticeException($message, $type, $type, $file, $line);
            case E_CORE_ERROR: throw new Exceptions\CoreErrorException($message, $type, $type, $file, $line);
            case E_CORE_WARNING: throw new Exceptions\CoreWarningException($message, $type, $type, $file, $line);
            case E_COMPILE_ERROR: throw new Exceptions\CompileErrorException($message, $type, $type, $file, $line);
            case E_COMPILE_WARNING: throw new Exceptions\CompileWarningException($message, $type, $type, $file, $line);
            case E_USER_ERROR: throw new Exceptions\UserErrorException($message, $type, $type, $file, $line);
            case E_USER_WARNING: throw new Exceptions\UserWarningException($message, $type, $type, $file, $line);
            case E_USER_NOTICE: throw new Exceptions\UserNoticeException($message, $type, $type, $file, $line);
            case E_STRICT: throw new Exceptions\StrictErrorException($message, $type, $type, $file, $line);
            case E_RECOVERABLE_ERROR: throw new Exceptions\RecoverableErrorException($message, $type, $type, $file, $line);
            case E_DEPRECATED: throw new Exceptions\DepricatedException($message, $type, $type, $file, $line);
            case E_USER_DEPRECATED: throw new Exceptions\UserDeprecatedException($message, $type, $type, $file, $line);
            default: throw new ErrorException($message, $type, $type, $file, $line);
        }
    }
}
