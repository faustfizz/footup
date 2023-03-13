<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Footup\Cli\Output;

/**
 * Cli Writer.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 *
 * @method Writer bgBlack($text, $eol = false)
 * @method Writer bgBlue($text, $eol = false)
 * @method Writer bgCyan($text, $eol = false)
 * @method Writer bgGreen($text, $eol = false)
 * @method Writer bgPurple($text, $eol = false)
 * @method Writer bgRed($text, $eol = false)
 * @method Writer bgWhite($text, $eol = false)
 * @method Writer bgYellow($text, $eol = false)
 * @method Writer black($text, $eol = false)
 * @method Writer blackBgBlue($text, $eol = false)
 * @method Writer blackBgCyan($text, $eol = false)
 * @method Writer blackBgGreen($text, $eol = false)
 * @method Writer blackBgPurple($text, $eol = false)
 * @method Writer blackBgRed($text, $eol = false)
 * @method Writer blackBgWhite($text, $eol = false)
 * @method Writer blackBgYellow($text, $eol = false)
 * @method Writer blue($text, $eol = false)
 * @method Writer blueBgBlack($text, $eol = false)
 * @method Writer blueBgCyan($text, $eol = false)
 * @method Writer blueBgGreen($text, $eol = false)
 * @method Writer blueBgPurple($text, $eol = false)
 * @method Writer blueBgRed($text, $eol = false)
 * @method Writer blueBgWhite($text, $eol = false)
 * @method Writer blueBgYellow($text, $eol = false)
 * @method Writer bold($text, $eol = false)
 * @method Writer boldBlack($text, $eol = false)
 * @method Writer boldBlackBgBlue($text, $eol = false)
 * @method Writer boldBlackBgCyan($text, $eol = false)
 * @method Writer boldBlackBgGreen($text, $eol = false)
 * @method Writer boldBlackBgPurple($text, $eol = false)
 * @method Writer boldBlackBgRed($text, $eol = false)
 * @method Writer boldBlackBgWhite($text, $eol = false)
 * @method Writer boldBlackBgYellow($text, $eol = false)
 * @method Writer boldBlue($text, $eol = false)
 * @method Writer boldBlueBgBlack($text, $eol = false)
 * @method Writer boldBlueBgCyan($text, $eol = false)
 * @method Writer boldBlueBgGreen($text, $eol = false)
 * @method Writer boldBlueBgPurple($text, $eol = false)
 * @method Writer boldBlueBgRed($text, $eol = false)
 * @method Writer boldBlueBgWhite($text, $eol = false)
 * @method Writer boldBlueBgYellow($text, $eol = false)
 * @method Writer boldCyan($text, $eol = false)
 * @method Writer boldCyanBgBlack($text, $eol = false)
 * @method Writer boldCyanBgBlue($text, $eol = false)
 * @method Writer boldCyanBgGreen($text, $eol = false)
 * @method Writer boldCyanBgPurple($text, $eol = false)
 * @method Writer boldCyanBgRed($text, $eol = false)
 * @method Writer boldCyanBgWhite($text, $eol = false)
 * @method Writer boldCyanBgYellow($text, $eol = false)
 * @method Writer boldGreen($text, $eol = false)
 * @method Writer boldGreenBgBlack($text, $eol = false)
 * @method Writer boldGreenBgBlue($text, $eol = false)
 * @method Writer boldGreenBgCyan($text, $eol = false)
 * @method Writer boldGreenBgPurple($text, $eol = false)
 * @method Writer boldGreenBgRed($text, $eol = false)
 * @method Writer boldGreenBgWhite($text, $eol = false)
 * @method Writer boldGreenBgYellow($text, $eol = false)
 * @method Writer boldPurple($text, $eol = false)
 * @method Writer boldPurpleBgBlack($text, $eol = false)
 * @method Writer boldPurpleBgBlue($text, $eol = false)
 * @method Writer boldPurpleBgCyan($text, $eol = false)
 * @method Writer boldPurpleBgGreen($text, $eol = false)
 * @method Writer boldPurpleBgRed($text, $eol = false)
 * @method Writer boldPurpleBgWhite($text, $eol = false)
 * @method Writer boldPurpleBgYellow($text, $eol = false)
 * @method Writer boldRed($text, $eol = false)
 * @method Writer boldRedBgBlack($text, $eol = false)
 * @method Writer boldRedBgBlue($text, $eol = false)
 * @method Writer boldRedBgCyan($text, $eol = false)
 * @method Writer boldRedBgGreen($text, $eol = false)
 * @method Writer boldRedBgPurple($text, $eol = false)
 * @method Writer boldRedBgWhite($text, $eol = false)
 * @method Writer boldRedBgYellow($text, $eol = false)
 * @method Writer boldWhite($text, $eol = false)
 * @method Writer boldWhiteBgBlack($text, $eol = false)
 * @method Writer boldWhiteBgBlue($text, $eol = false)
 * @method Writer boldWhiteBgCyan($text, $eol = false)
 * @method Writer boldWhiteBgGreen($text, $eol = false)
 * @method Writer boldWhiteBgPurple($text, $eol = false)
 * @method Writer boldWhiteBgRed($text, $eol = false)
 * @method Writer boldWhiteBgYellow($text, $eol = false)
 * @method Writer boldYellow($text, $eol = false)
 * @method Writer boldYellowBgBlack($text, $eol = false)
 * @method Writer boldYellowBgBlue($text, $eol = false)
 * @method Writer boldYellowBgCyan($text, $eol = false)
 * @method Writer boldYellowBgGreen($text, $eol = false)
 * @method Writer boldYellowBgPurple($text, $eol = false)
 * @method Writer boldYellowBgRed($text, $eol = false)
 * @method Writer boldYellowBgWhite($text, $eol = false)
 * @method Writer colors($text)
 * @method Writer comment($text, $eol = false)
 * @method Writer cyan($text, $eol = false)
 * @method Writer cyanBgBlack($text, $eol = false)
 * @method Writer cyanBgBlue($text, $eol = false)
 * @method Writer cyanBgGreen($text, $eol = false)
 * @method Writer cyanBgPurple($text, $eol = false)
 * @method Writer cyanBgRed($text, $eol = false)
 * @method Writer cyanBgWhite($text, $eol = false)
 * @method Writer cyanBgYellow($text, $eol = false)
 * @method Writer error($text, $eol = false)
 * @method Writer green($text, $eol = false)
 * @method Writer greenBgBlack($text, $eol = false)
 * @method Writer greenBgBlue($text, $eol = false)
 * @method Writer greenBgCyan($text, $eol = false)
 * @method Writer greenBgPurple($text, $eol = false)
 * @method Writer greenBgRed($text, $eol = false)
 * @method Writer greenBgWhite($text, $eol = false)
 * @method Writer greenBgYellow($text, $eol = false)
 * @method Writer info($text, $eol = false)
 * @method Writer ok($text, $eol = false)
 * @method Writer success($text, $eol = false)
 * @method Writer purple($text, $eol = false)
 * @method Writer purpleBgBlack($text, $eol = false)
 * @method Writer purpleBgBlue($text, $eol = false)
 * @method Writer purpleBgCyan($text, $eol = false)
 * @method Writer purpleBgGreen($text, $eol = false)
 * @method Writer purpleBgRed($text, $eol = false)
 * @method Writer purpleBgWhite($text, $eol = false)
 * @method Writer purpleBgYellow($text, $eol = false)
 * @method Writer red($text, $eol = false)
 * @method Writer redBgBlack($text, $eol = false)
 * @method Writer redBgBlue($text, $eol = false)
 * @method Writer redBgCyan($text, $eol = false)
 * @method Writer redBgGreen($text, $eol = false)
 * @method Writer redBgPurple($text, $eol = false)
 * @method Writer redBgWhite($text, $eol = false)
 * @method Writer redBgYellow($text, $eol = false)
 * @method Writer warn($text, $eol = false)
 * @method Writer white($text, $eol = false)
 * @method Writer yellow($text, $eol = false)
 * @method Writer yellowBgBlack($text, $eol = false)
 * @method Writer yellowBgBlue($text, $eol = false)
 * @method Writer yellowBgCyan($text, $eol = false)
 * @method Writer yellowBgGreen($text, $eol = false)
 * @method Writer yellowBgPurple($text, $eol = false)
 * @method Writer yellowBgRed($text, $eol = false)
 * @method Writer yellowBgWhite($text, $eol = false)
 */
class Writer
{
    /** @var resource Output file handle */
    protected $stream;

    /** @var resource Error output file handle */
    protected $eStream;

    /** @var string Write method to be relayed to Colorizer */
    protected $method;

    /** @var Color */
    protected $colorizer;

    /** @var Cursor */
    protected $cursor;

    public function __construct(string $path = null, Color $colorizer = null)
    {
        if ($path) {
            $path = \fopen($path, 'w');
        }

        $this->stream  = $path ?: \STDOUT;
        $this->eStream = $path ?: \STDERR;

        $this->cursor    = new Cursor;
        $this->colorizer = $colorizer ?? new Color;
    }

    /**
     * Get Colorizer.
     *
     * @return Color
     */
    public function colorizer(): Color
    {
        return $this->colorizer;
    }

    /**
     * Magically set methods.
     *
     * @param string $name Like `red`, `bgRed`, 'bold', `error` etc
     *
     * @return self
     */
    public function __get(string $name): self
    {
        if (\strpos($this->method, $name) === false) {
            $this->method .= $this->method ? \ucfirst($name) : $name;
        }

        return $this;
    }

    /**
     * Write the formatted text to stdout or stderr.
     *
     * @param string $text
     * @param bool   $eol
     *
     * @return self
     */
    public function write(string $text, bool $eol = false): self
    {
        list($method, $this->method) = [$this->method ?: 'line', ''];

        $text  = $this->colorizer->{$method}($text, []);
        $error = \stripos($method, 'error') !== false;

        if ($eol) {
            $text .= \PHP_EOL;
        }

        return $this->doWrite($text, $error);
    }

    /**
     * Really write to the stream.
     *
     * @param string $text
     * @param bool   $error
     *
     * @return self
     */
    protected function doWrite(string $text, bool $error = false): self
    {
        $stream = $error ? $this->eStream : $this->stream;

        \fwrite($stream, $text);

        return $this;
    }

    /**
     * Write EOL n times.
     *
     * @param int $n
     *
     * @return self
     */
    public function eol(int $n = 1): self
    {
        return $this->doWrite(\str_repeat(PHP_EOL, \max($n, 1)));
    }

    /**
     * Write raw text (as it is).
     *
     * @param string $text
     * @param bool   $error
     *
     * @return self
     */
    public function raw($text, bool $error = false): self
    {
        return $this->doWrite((string) $text, $error);
    }

    /**
     * Generate table for the console. Keys of first row are taken as header.
     *
     * @param array[] $rows   Array of assoc arrays.
     * @param array   $styles Eg: ['head' => 'bold', 'odd' => 'comment', 'even' => 'green']
     *
     * @return self
     */
    public function table(array $rows, array $styles = []): self
    {
        $table = (new Table)->render($rows, $styles);

        return $this->colors($table);
    }

    /**
     * Write to stdout or stderr magically.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return self
     */
    public function __call(string $method, array $arguments): self
    {
        if (\method_exists($this->cursor, $method)) {
            return $this->doWrite($this->cursor->{$method}(...$arguments));
        }

        $this->method = $method;

        return $this->write(...$arguments);
    }
}
