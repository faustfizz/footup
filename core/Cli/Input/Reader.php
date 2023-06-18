<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Footup\Cli\Input;

/**
 * Cli Reader.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Reader
{
    /** @var resource Input file handle */
    protected $stream;

    /**
     * Constructor.
     *
     * @param string|null $path Read path. Defaults to STDIN.
     */
    public function __construct(string $path = null)
    {
        $this->stream = $path ? \fopen($path, 'r') : \STDIN;
    }

    /**
     * Read a line from configured stream (or terminal).
     *
     * @param mixed         $default The default value.
     * @param callable|null $fn      The validator/sanitizer callback.
     *
     * @return mixed
     */
    public function read($default = null, callable $fn = null)
    {
        $in = \rtrim(\fgets($this->stream), "\r\n");

        if ('' === $in && null !== $default) {
            return $default;
        }

        return $fn ? $fn($in) : $in;
    }

    /**
     * Same like read but it reads all the lines.
     *
     * @codeCoverageIgnore
     *
     * @param callable|null $fn The validator/sanitizer callback.
     *
     * @return string
     */
    public function readAll(callable $fn = null): string
    {
        $in = \stream_get_contents($this->stream);

        return $fn ? $fn($in) : $in;
    }

    /**
     * Read content piped to the stream without waiting.
     *
     * @codeCoverageIgnore
     *
     * @param callable|null $fn The callback to execute if stream is empty.
     *
     * @return string
     */
    public function readPiped(callable $fn = null): string
    {
        $stdin = '';
        $read  = [$this->stream];
        $write = [];
        $exept = [];

        if (\stream_select($read, $write, $exept, 0) === 1) {
            while ($line = \fgets($this->stream)) {
                $stdin .= $line;
            }
        }

        if ('' === $stdin) {
            return $fn ? $fn($this) : '';
        }

        return $stdin;
    }

    /**
     * Read a line from configured stream (or terminal) but don't echo it back.
     *
     * @param callable|null $fn The validator/sanitizer callback.
     *
     * @return mixed
     */
    public function readHidden($default = null, callable $fn = null)
    {
        // @codeCoverageIgnoreStart
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return $this->readHiddenWinOS($default, $fn);
        }
        // @codeCoverageIgnoreEnd

        \shell_exec('stty -echo');
        $in = $this->read($default, $fn);
        \shell_exec('stty echo');

        echo \PHP_EOL;

        return $in;
    }

    /**
     * Read a line from configured stream (or terminal) but don't echo it back.
     *
     * @codeCoverageIgnore
     *
     * @param callable|null $fn The validator/sanitizer callback.
     *
     * @return mixed
     */
    protected function readHiddenWinOS($default = null, callable $fn = null)
    {
        $cmd = 'powershell -Command ' . \implode('; ', \array_filter([
            '$pword = Read-Host -AsSecureString',
            '$pword = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($pword)',
            '$pword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($pword)',
            'echo $pword',
        ]));

        $in = \rtrim(\shell_exec($cmd), "\r\n");

        if ('' === $in && null !== $default) {
            return $default;
        }

        return $fn ? $fn($in) : $in;
    }
}
