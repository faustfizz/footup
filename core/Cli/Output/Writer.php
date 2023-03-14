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
 * @method Writer bgBlack($text, $eol = false, $style = [])
 * @method Writer bgBlue($text, $eol = false, $style = [])
 * @method Writer bgCyan($text, $eol = false, $style = [])
 * @method Writer bgGreen($text, $eol = false, $style = [])
 * @method Writer bgPurple($text, $eol = false, $style = [])
 * @method Writer bgRed($text, $eol = false, $style = [])
 * @method Writer bgWhite($text, $eol = false, $style = [])
 * @method Writer bgYellow($text, $eol = false, $style = [])
 * @method Writer black($text, $eol = false, $style = [])
 * @method Writer blackBgBlue($text, $eol = false, $style = [])
 * @method Writer blackBgCyan($text, $eol = false, $style = [])
 * @method Writer blackBgGreen($text, $eol = false, $style = [])
 * @method Writer blackBgPurple($text, $eol = false, $style = [])
 * @method Writer blackBgRed($text, $eol = false, $style = [])
 * @method Writer blackBgWhite($text, $eol = false, $style = [])
 * @method Writer blackBgYellow($text, $eol = false, $style = [])
 * @method Writer blue($text, $eol = false, $style = [])
 * @method Writer blueBgBlack($text, $eol = false, $style = [])
 * @method Writer blueBgCyan($text, $eol = false, $style = [])
 * @method Writer blueBgGreen($text, $eol = false, $style = [])
 * @method Writer blueBgPurple($text, $eol = false, $style = [])
 * @method Writer blueBgRed($text, $eol = false, $style = [])
 * @method Writer blueBgWhite($text, $eol = false, $style = [])
 * @method Writer blueBgYellow($text, $eol = false, $style = [])
 * @method Writer bold($text, $eol = false, $style = [])
 * @method Writer boldBlack($text, $eol = false, $style = [])
 * @method Writer boldBlackBgBlue($text, $eol = false, $style = [])
 * @method Writer boldBlackBgCyan($text, $eol = false, $style = [])
 * @method Writer boldBlackBgGreen($text, $eol = false, $style = [])
 * @method Writer boldBlackBgPurple($text, $eol = false, $style = [])
 * @method Writer boldBlackBgRed($text, $eol = false, $style = [])
 * @method Writer boldBlackBgWhite($text, $eol = false, $style = [])
 * @method Writer boldBlackBgYellow($text, $eol = false, $style = [])
 * @method Writer boldBlue($text, $eol = false, $style = [])
 * @method Writer boldBlueBgBlack($text, $eol = false, $style = [])
 * @method Writer boldBlueBgCyan($text, $eol = false, $style = [])
 * @method Writer boldBlueBgGreen($text, $eol = false, $style = [])
 * @method Writer boldBlueBgPurple($text, $eol = false, $style = [])
 * @method Writer boldBlueBgRed($text, $eol = false, $style = [])
 * @method Writer boldBlueBgWhite($text, $eol = false, $style = [])
 * @method Writer boldBlueBgYellow($text, $eol = false, $style = [])
 * @method Writer boldCyan($text, $eol = false, $style = [])
 * @method Writer boldCyanBgBlack($text, $eol = false, $style = [])
 * @method Writer boldCyanBgBlue($text, $eol = false, $style = [])
 * @method Writer boldCyanBgGreen($text, $eol = false, $style = [])
 * @method Writer boldCyanBgPurple($text, $eol = false, $style = [])
 * @method Writer boldCyanBgRed($text, $eol = false, $style = [])
 * @method Writer boldCyanBgWhite($text, $eol = false, $style = [])
 * @method Writer boldCyanBgYellow($text, $eol = false, $style = [])
 * @method Writer boldGreen($text, $eol = false, $style = [])
 * @method Writer boldGreenBgBlack($text, $eol = false, $style = [])
 * @method Writer boldGreenBgBlue($text, $eol = false, $style = [])
 * @method Writer boldGreenBgCyan($text, $eol = false, $style = [])
 * @method Writer boldGreenBgPurple($text, $eol = false, $style = [])
 * @method Writer boldGreenBgRed($text, $eol = false, $style = [])
 * @method Writer boldGreenBgWhite($text, $eol = false, $style = [])
 * @method Writer boldGreenBgYellow($text, $eol = false, $style = [])
 * @method Writer boldPurple($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgBlack($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgBlue($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgCyan($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgGreen($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgRed($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgWhite($text, $eol = false, $style = [])
 * @method Writer boldPurpleBgYellow($text, $eol = false, $style = [])
 * @method Writer boldRed($text, $eol = false, $style = [])
 * @method Writer boldRedBgBlack($text, $eol = false, $style = [])
 * @method Writer boldRedBgBlue($text, $eol = false, $style = [])
 * @method Writer boldRedBgCyan($text, $eol = false, $style = [])
 * @method Writer boldRedBgGreen($text, $eol = false, $style = [])
 * @method Writer boldRedBgPurple($text, $eol = false, $style = [])
 * @method Writer boldRedBgWhite($text, $eol = false, $style = [])
 * @method Writer boldRedBgYellow($text, $eol = false, $style = [])
 * @method Writer boldWhite($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgBlack($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgBlue($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgCyan($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgGreen($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgPurple($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgRed($text, $eol = false, $style = [])
 * @method Writer boldWhiteBgYellow($text, $eol = false, $style = [])
 * @method Writer boldYellow($text, $eol = false, $style = [])
 * @method Writer boldYellowBgBlack($text, $eol = false, $style = [])
 * @method Writer boldYellowBgBlue($text, $eol = false, $style = [])
 * @method Writer boldYellowBgCyan($text, $eol = false, $style = [])
 * @method Writer boldYellowBgGreen($text, $eol = false, $style = [])
 * @method Writer boldYellowBgPurple($text, $eol = false, $style = [])
 * @method Writer boldYellowBgRed($text, $eol = false, $style = [])
 * @method Writer boldYellowBgWhite($text, $eol = false, $style = [])
 * @method Writer colors($text)
 * @method Writer comment($text, $eol = false, $style = [])
 * @method Writer cyan($text, $eol = false, $style = [])
 * @method Writer cyanBgBlack($text, $eol = false, $style = [])
 * @method Writer cyanBgBlue($text, $eol = false, $style = [])
 * @method Writer cyanBgGreen($text, $eol = false, $style = [])
 * @method Writer cyanBgPurple($text, $eol = false, $style = [])
 * @method Writer cyanBgRed($text, $eol = false, $style = [])
 * @method Writer cyanBgWhite($text, $eol = false, $style = [])
 * @method Writer cyanBgYellow($text, $eol = false, $style = [])
 * @method Writer error($text, $eol = false, $style = [])
 * @method Writer green($text, $eol = false, $style = [])
 * @method Writer greenBgBlack($text, $eol = false, $style = [])
 * @method Writer greenBgBlue($text, $eol = false, $style = [])
 * @method Writer greenBgCyan($text, $eol = false, $style = [])
 * @method Writer greenBgPurple($text, $eol = false, $style = [])
 * @method Writer greenBgRed($text, $eol = false, $style = [])
 * @method Writer greenBgWhite($text, $eol = false, $style = [])
 * @method Writer greenBgYellow($text, $eol = false, $style = [])
 * @method Writer info($text, $eol = false, $style = [])
 * @method Writer ok($text, $eol = false, $style = [])
 * @method Writer success($text, $eol = false, $style = [])
 * @method Writer purple($text, $eol = false, $style = [])
 * @method Writer purpleBgBlack($text, $eol = false, $style = [])
 * @method Writer purpleBgBlue($text, $eol = false, $style = [])
 * @method Writer purpleBgCyan($text, $eol = false, $style = [])
 * @method Writer purpleBgGreen($text, $eol = false, $style = [])
 * @method Writer purpleBgRed($text, $eol = false, $style = [])
 * @method Writer purpleBgWhite($text, $eol = false, $style = [])
 * @method Writer purpleBgYellow($text, $eol = false, $style = [])
 * @method Writer red($text, $eol = false, $style = [])
 * @method Writer redBgBlack($text, $eol = false, $style = [])
 * @method Writer redBgBlue($text, $eol = false, $style = [])
 * @method Writer redBgCyan($text, $eol = false, $style = [])
 * @method Writer redBgGreen($text, $eol = false, $style = [])
 * @method Writer redBgPurple($text, $eol = false, $style = [])
 * @method Writer redBgWhite($text, $eol = false, $style = [])
 * @method Writer redBgYellow($text, $eol = false, $style = [])
 * @method Writer warn($text, $eol = false, $style = [])
 * @method Writer white($text, $eol = false, $style = [])
 * @method Writer yellow($text, $eol = false, $style = [])
 * @method Writer yellowBgBlack($text, $eol = false, $style = [])
 * @method Writer yellowBgBlue($text, $eol = false, $style = [])
 * @method Writer yellowBgCyan($text, $eol = false, $style = [])
 * @method Writer yellowBgGreen($text, $eol = false, $style = [])
 * @method Writer yellowBgPurple($text, $eol = false, $style = [])
 * @method Writer yellowBgRed($text, $eol = false, $style = [])
 * @method Writer yellowBgWhite($text, $eol = false, $style = [])
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
     * @param array $style
     * @param bool   $eol
     *
     * @return self
     */
    public function write(string $text, bool $eol = false, $style = []): self
    {
        list($method, $this->method) = [$this->method ?: 'line', ''];

        $text  = $this->colorizer->{$method}($text, $style);
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
