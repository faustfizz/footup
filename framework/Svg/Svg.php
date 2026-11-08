<?php

declare(strict_types=1);

namespace Footup\Svg;

/**
 * Facade for easy SVG text generation
 * 
 * Usage:
 *   Svg::text('Hello')->save('hello.svg');
 *   Svg::create('World')->fontSize(48)->color('#FF0000')->get();
 */
class Svg
{
    /**
     * Create new SVG builder with text
     */
    public static function text(string $text): SvgBuilder
    {
        return new SvgBuilder($text);
    }

    /**
     * Alias for text()
     */
    public static function create(string $text): SvgBuilder
    {
        return new SvgBuilder($text);
    }

    /**
     * Start with empty builder
     */
    public static function builder(): SvgBuilder
    {
        return new SvgBuilder();
    }
}
