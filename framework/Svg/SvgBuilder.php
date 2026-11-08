<?php

declare(strict_types=1);

namespace Footup\Svg;

/**
 * Simple SVG text generator with fluent API
 * Generates SVG images from text without external dependencies
 */
class SvgBuilder
{
    protected string $text = '';
    protected int $fontSize = 24;
    protected string $fontFamily = 'Arial, sans-serif';
    protected string $color = '#000000';
    protected string $textAnchor = 'middle';
    protected int $padding = 20;
    protected string $backgroundColor = 'transparent';
    protected string $fontWeight = 'normal';
    protected string $fontStyle = 'normal';
    protected int $letterSpacing = 0;
    protected int $lineHeight = 1;

    public function __construct(string $text = '')
    {
        $this->text = $text;
    }

    /**
     * Set the text content
     */
    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set font size in pixels
     */
    public function fontSize(int $size): self
    {
        $this->fontSize = $size;
        return $this;
    }

    /**
     * Set font family (e.g., 'Arial', 'Georgia', 'Courier New')
     * Multiple fonts can be specified: 'Georgia, serif'
     */
    public function fontFamily(string $family): self
    {
        $this->fontFamily = $family;
        return $this;
    }

    /**
     * Alias for fontFamily()
     */
    public function font(string $family): self
    {
        return $this->fontFamily($family);
    }

    /**
     * Set text color (hex format: #RRGGBB)
     */
    public function color(string $hex): self
    {
        $this->color = $hex;
        return $this;
    }

    /**
     * Set background color (hex format: #RRGGBB or 'transparent')
     */
    public function backgroundColor(string $color): self
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Set font weight (normal, bold, 100-900)
     */
    public function fontWeight(string $weight): self
    {
        $this->fontWeight = $weight;
        return $this;
    }

    /**
     * Set font style (normal, italic, oblique)
     */
    public function fontStyle(string $style): self
    {
        $this->fontStyle = $style;
        return $this;
    }

    /**
     * Set text anchor alignment (start, middle, end)
     */
    public function align(string $anchor): self
    {
        $this->textAnchor = match ($anchor) {
            'left' => 'start',
            'center', 'middle' => 'middle',
            'right', 'end' => 'end',
            default => $anchor
        };
        return $this;
    }

    /**
     * Set padding around text in pixels
     */
    public function padding(int $padding): self
    {
        $this->padding = $padding;
        return $this;
    }

    /**
     * Set letter spacing in pixels
     */
    public function letterSpacing(int $spacing): self
    {
        $this->letterSpacing = $spacing;
        return $this;
    }

    /**
     * Set line height multiplier (for multi-line text)
     */
    public function lineHeight(int $height): self
    {
        $this->lineHeight = $height;
        return $this;
    }

    /**
     * Calculate text dimensions (approximate)
     * Returns ['width' => ..., 'height' => ...]
     */
    protected function calculateDimensions(): array
    {
        // Rough estimate: each character is ~0.6 of the font size
        // This is approximate and varies by font
        $charWidth = $this->fontSize * 0.6;
        $width = (int)(strlen($this->text) * $charWidth + $this->letterSpacing * strlen($this->text));
        $height = $this->fontSize;

        // Handle line breaks
        $lines = explode("\n", $this->text);
        if (count($lines) > 1) {
            $height = (int)($this->fontSize * $this->lineHeight * count($lines));
            // Find longest line
            $maxWidth = 0;
            foreach ($lines as $line) {
                $lineWidth = (int)(strlen($line) * $charWidth + $this->letterSpacing * strlen($line));
                $maxWidth = max($maxWidth, $lineWidth);
            }
            $width = $maxWidth;
        }

        return [
            'width' => max(100, $width),  // Minimum 100px
            'height' => max(50, $height)   // Minimum 50px
        ];
    }

    /**
     * Generate SVG string
     */
    public function generate(): string
    {
        $dimensions = $this->calculateDimensions();
        $width = $dimensions['width'] + (2 * $this->padding);
        $height = $dimensions['height'] + (2 * $this->padding);

        // Escape HTML entities in text
        $escapedText = htmlspecialchars($this->text, ENT_XML1, 'UTF-8');

        // Handle multi-line text
        $lines = explode("\n", $this->text);
        $textElements = '';

        if (count($lines) === 1) {
            // Single line
            $x = $width / 2;
            $y = $this->padding + $this->fontSize;

            $textElements = sprintf(
                '<text x="%d" y="%d" font-family="%s" font-size="%d" font-weight="%s" font-style="%s" fill="%s" text-anchor="%s" letter-spacing="%d">%s</text>',
                (int)$x,
                (int)$y,
                htmlspecialchars($this->fontFamily),
                $this->fontSize,
                $this->fontWeight,
                $this->fontStyle,
                $this->color,
                $this->textAnchor,
                $this->letterSpacing,
                $escapedText
            );
        } else {
            // Multi-line text
            $x = $width / 2;
            $lineHeightPx = $this->fontSize * $this->lineHeight;
            $startY = $this->padding + $this->fontSize;

            foreach ($lines as $index => $line) {
                $y = $startY + ($index * $lineHeightPx);
                $escapedLine = htmlspecialchars($line, ENT_XML1, 'UTF-8');

                $textElements .= sprintf(
                    '<text x="%d" y="%d" font-family="%s" font-size="%d" font-weight="%s" font-style="%s" fill="%s" text-anchor="%s" letter-spacing="%d">%s</text>',
                    (int)$x,
                    (int)$y,
                    htmlspecialchars($this->fontFamily),
                    $this->fontSize,
                    $this->fontWeight,
                    $this->fontStyle,
                    $this->color,
                    $this->textAnchor,
                    $this->letterSpacing,
                    $escapedLine
                );
                $textElements .= "\n";
            }
        }

        // Build background if needed
        $background = '';
        if ($this->backgroundColor !== 'transparent') {
            $background = sprintf(
                '<rect width="%d" height="%d" fill="%s"/>',
                $width,
                $height,
                $this->backgroundColor
            );
        }

        // Build SVG
        $svg = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<svg width="%d" height="%d" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
%s
%s
</svg>',
            $width,
            $height,
            $background ? "  " . $background . "\n" : "",
            $textElements
        );

        return $svg;
    }

    /**
     * Get SVG as string
     */
    public function get(): string
    {
        return $this->generate();
    }

    /**
     * Save SVG to file
     */
    public function save(string $path): string
    {
        $svg = $this->generate();
        file_put_contents($path, $svg);
        return $path;
    }

    /**
     * Return SVG as response (for HTTP)
     */
    public function response(): array
    {
        return [
            'content' => $this->generate(),
            'type' => 'image/svg+xml'
        ];
    }
}
