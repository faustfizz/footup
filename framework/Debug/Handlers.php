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
use Footup\Engine\Application;

class Handlers implements HandlersInterface
{
    /**
     * envirenment variable.
     *
     * @var string
     */
    public $env = 'dev';
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
        $this->whichError($this->translateErrorMessage($message), $type, $file, $line);
    }

    public function translateErrorMessage(string $message) {
        if (function_exists('text')) {
            return text('Http.error500', [Application::NAME, $message]);
        }
        return $message;
    }

    /**
     * exception handler.
     *
     * @param \Exception $e exception object
     *
     * @return string throw exception based on the error type
     */
    public function exceptionHandler($e)
    {
        $this->setError(
            (int) $e->getCode(),
            (string) $this->translateErrorMessage($e->getMessage()),
            (string) $e->getFile(),
            (int) $e->getLine(),
            (string) get_class($e),
            $e->getTrace()
        );

        return View::render('500.php', (object) $this->errors, $this->env);
    }

    /**
     * error handler method.
     *
     * @return void
     */
    public function fatalHandler()
    {
        $errors = error_get_last();
        if (is_array($errors)) {
            $this->setError(
                (int) $errors['type'],
                (string) $this->translateErrorMessage($errors['message']),
                (string) $errors['file'],
                (int) $errors['line'],
                'FatalErrorException'
            );
            
            return View::render('500.php', (object) $this->errors, $this->env);
        }
    }

    public static function handleExceptionError(\Throwable $th) {
      $handler = new self;
      
      $errorException = $handler->whichError(
        $th->getMessage(),
        $th->getCode(),
        $th->getFile(),
        $th->getLine(),
      );

      return $handler->exceptionHandler(
        $errorException
      );
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
     * @throws \Footup\Exceptions\WarningException
     * @throws \Footup\Exceptions\ParseErrorException
     * @throws \Footup\Exceptions\NoticeException
     * @throws \Footup\Exceptions\CoreErrorException
     * @throws \Footup\Exceptions\CompileErrorException
     * @throws \Footup\Exceptions\CompileWarningException
     * @throws \Footup\Exceptions\UserErrorException
     * @throws \Footup\Exceptions\UserWarningException
     * @throws \Footup\Exceptions\UserNoticeException
     * @throws \Footup\Exceptions\StrictErrorException
     * @throws \Footup\Exceptions\RecoverableErrorException
     * @throws \Footup\Exceptions\DepricatedException
     * @throws \Footup\Exceptions\UserDeprecatedException
     * @throws ErrorException
     */
    public function whichError(string $message, int $type, string $file, int $line)
    {
        return match ($type) {
            E_ERROR => new ErrorException($message, $type, $type, $file, $line),
            E_WARNING => new \Footup\Exceptions\WarningException($message, $type, $type, $file, $line),
            E_PARSE => new \Footup\Exceptions\ParseErrorException($message, $type, $type, $file, $line),
            E_NOTICE => new \Footup\Exceptions\NoticeException($message, $type, $type, $file, $line),
            E_CORE_ERROR => new \Footup\Exceptions\CoreErrorException($message, $type, $type, $file, $line),
            E_CORE_WARNING => new \Footup\Exceptions\CoreWarningException($message, $type, $type, $file, $line),
            E_COMPILE_ERROR => new \Footup\Exceptions\CompileErrorException($message, $type, $type, $file, $line),
            E_COMPILE_WARNING => new \Footup\Exceptions\CompileWarningException($message, $type, $type, $file, $line),
            E_USER_ERROR => new \Footup\Exceptions\UserErrorException($message, $type, $type, $file, $line),
            E_USER_WARNING => new \Footup\Exceptions\UserWarningException($message, $type, $type, $file, $line),
            E_USER_NOTICE => new \Footup\Exceptions\UserNoticeException($message, $type, $type, $file, $line),
            E_STRICT => new \Footup\Exceptions\StrictErrorException($message, $type, $type, $file, $line),
            E_RECOVERABLE_ERROR => new \Footup\Exceptions\RecoverableErrorException($message, $type, $type, $file, $line),
            E_DEPRECATED => new \Footup\Exceptions\DepricatedException($message, $type, $type, $file, $line),
            E_USER_DEPRECATED => new \Footup\Exceptions\UserDeprecatedException($message, $type, $type, $file, $line),
            default => new ErrorException($message, $type, $type, $file, $line)
        };
    }
}
