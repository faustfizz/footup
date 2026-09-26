<?php

namespace Footup\View;

use Footup\Exceptions\SyntaxError;

/**
 * Template Parser - Convert Latte syntax to AST
 * 
 * Parses:
 * - Variables: {$var}, {$obj->prop}
 * - Filters: {$var|filter:arg}
 * - No-escape: {! $html !}
 * - Declarations: {var $name = value}, {set $name = value}
 * - Capture: {capture $var}...{/capture}
 * - Control: {if}, {foreach}, {while}, {for}
 * - Comments: {* comment *}
 * - Attributes: ui:if, ui:foreach, :if, :foreach
 * - Layouts: {extends}, {block}, {include}
 */
class Parser
{
    /**
     * @var string Input source
     */
    private string $source = '';

    /**
     * @var int Current position in source
     */
    private int $pos = 0;

    /**
     * @var int Line number (for error reporting)
     */
    private int $line = 1;

    /**
     * @var array Parsed AST nodes
     */
    private array $nodes = [];

    /**
     * @var array Options
     */
    private array $options = [];

    /**
     * Parse template source
     * 
     * @param string $source Template source code
     * @param array $options Parser options
     * @return array AST nodes
     * @throws SyntaxError
     */
    public function parse(string $source, array $options = []): array
    {
        $this->source = $source;
        $this->pos = 0;
        $this->line = 1;
        $this->nodes = [];
        $this->options = $options;

        while ($this->pos < strlen($this->source)) {
            $this->parseNext();
        }

        return $this->nodes;
    }

    /**
     * Parse next element from source
     * 
     * @throws SyntaxError
     */
    private function parseNext(): void
    {
        // Look for template tags
        if ($this->match('{*')) {
            // Comment
            $this->parseComment();
        } elseif ($this->match('{!')) {
            // No-escape output
            $this->parseNoEscapeOutput();
        } elseif ($this->match('{if')) {
            // If statement
            $this->parseIfStatement();
        } elseif ($this->match('{foreach')) {
            // Foreach loop
            $this->parseForeachLoop();
        } elseif ($this->match('{while')) {
            // While loop
            $this->parseWhileLoop();
        } elseif ($this->match('{for')) {
            // For loop
            $this->parseForLoop();
        } elseif ($this->match('{block')) {
            // Block definition
            $this->parseBlock();
        } elseif ($this->match('{/block')) {
            // Block end
            $this->pos += 7; // skip {/block}
            $this->expect('}');
            $this->nodes[] = ['type' => 'block_end'];
        } elseif ($this->match('{extends')) {
            // Layout extension
            $this->parseExtends();
        } elseif ($this->match('{include')) {
            // Include template
            $this->parseInclude();
        } elseif ($this->match('{$')) {
            // Variable output
            $this->parseOutput();
        } elseif ($this->match('{')) {
            $this->pos++; // skip comma
            $this->skipWhitespace();
            $this->nodes[] = $this->parseExpression();
        } else {
            // Plain text
            $this->parsePlainText();
        }
    }

    /**
     * Parse comment {* ... *}
     */
    private function parseComment(): void
    {
        $this->pos += 2; // skip {*
        
        while ($this->pos < strlen($this->source)) {
            if ($this->match('*}')) {
                $this->pos += 2;
                return;
            }
            if ($this->peek() === "\n") $this->line++;
            $this->pos++;
        }

        throw new SyntaxError("Unclosed comment at line {$this->line}");
    }

    /**
     * Parse no-escape output {! $var !}
     * 
     * @throws SyntaxError
     */
    private function parseNoEscapeOutput(): void
    {
        $this->pos += 2; // skip {!
        $this->skipWhitespace();

        $expr = $this->parseExpression();
        
        $this->skipWhitespace();
        if (!$this->match('!}')) {
            throw new SyntaxError("Expected !} at line {$this->line}");
        }
        $this->pos += 2;

        // Mark as no-escape
        $expr['type'] = 'output_raw';
        $this->nodes[] = $expr;
    }

    /**
     * Parse variable output {$var} or {$var|filter:arg}
     * 
     * @throws SyntaxError
     */
    private function parseOutput(): void
    {
        $this->pos += 2; // skip {$

        $var = $this->parseVariable();
        $filters = [];

        // Parse filters
        while ($this->peek() === '|') {
            $this->pos++; // skip |
            $filterName = $this->parseIdentifier();
            $filterArgs = [];

            // Parse filter arguments
            while ($this->peek() === ':') {
                $this->pos++; // skip :
                if ($this->peek() === "'") {
                    $filterArgs[] = $this->parseString();
                } else {
                    $filterArgs[] = $this->parseIdentifier();
                }
            }

            $filters[] = [
                'name' => $filterName,
                'args' => $filterArgs,
            ];
        }

        $this->skipWhitespace();
        if ($this->peek() !== '}') {
            throw new SyntaxError("Expected } at line {$this->line}");
        }
        $this->pos++;

        $this->nodes[] = [
            'type' => 'output',
            'var' => $var,
            'filters' => $filters,
            'line' => $this->line,
        ];
    }

    /**
     * Parse if statement {if condition}...{/if}
     * 
     * @throws SyntaxError
     */
    private function parseIfStatement(): void
    {
        $this->pos += 3; // skip {if
        $this->skipWhitespace();

        $condition = $this->parseCondition();
        
        $this->skipWhitespace();
        if ($this->peek() !== '}') {
            throw new SyntaxError("Expected } at line {$this->line}");
        }
        $this->pos++;

        $body = [];
        $elseifs = [];
        $else = null;

        // Parse body until {/if}, {elseif}, or {else}
        while ($this->pos < strlen($this->source)) {
            if ($this->match('{elseif')) {
                $this->pos += 8;
                $this->skipWhitespace();
                $elseifCondition = $this->parseCondition();
                $this->skipWhitespace();
                $this->expect('}');
                
                $elseifs[] = [
                    'condition' => $elseifCondition,
                    'body' => [],
                ];
                continue;
            } elseif ($this->match('{else')) {
                $this->pos += 5;
                $this->expect('}');
                $else = [];
                continue;
            } elseif ($this->match('{/if')) {
                $this->pos += 4;
                $this->expect('}');
                break;
            }

            // Store current position
            $startPos = $this->pos;
            $this->parseNext();
            
            // Add to appropriate container
            if (!empty($else)) {
                $else[] = end($this->nodes);
            } elseif (!empty($elseifs)) {
                $elseifs[count($elseifs) - 1]['body'][] = end($this->nodes);
            } else {
                $body[] = end($this->nodes);
            }
        }

        $this->nodes[] = [
            'type' => 'if',
            'condition' => $condition,
            'body' => $body,
            'elseifs' => $elseifs,
            'else' => $else,
        ];
    }

    /**
     * Parse foreach loop {foreach $items as $item}...{/foreach}
     * 
     * @throws SyntaxError
     */
    private function parseForeachLoop(): void
    {
        $this->pos += 8; // skip {foreach
        $this->skipWhitespace();

        // Parse: $array as $item or $array as $key => $value
        $array = $this->parseVariable();
        
        $this->skipWhitespace();
        if (!$this->match('as')) {
            throw new SyntaxError("Expected 'as' in foreach at line {$this->line}");
        }
        $this->pos += 2;
        
        $this->skipWhitespace();
        $key = null;
        $value = $this->parseVariable();

        // Check for key => value syntax
        if ($this->match('=>')) {
            $this->pos += 2;
            $this->skipWhitespace();
            $key = $value;
            $value = $this->parseVariable();
        }

        $this->skipWhitespace();
        $this->expect('}');

        $body = [];

        while ($this->pos < strlen($this->source)) {
            if ($this->match('{/foreach')) {
                $this->pos += 10;
                $this->expect('}');
                break;
            }

            $this->parseNext();
            $body[] = end($this->nodes);
        }

        $this->nodes[] = [
            'type' => 'foreach',
            'array' => $array,
            'key' => $key,
            'value' => $value,
            'body' => $body,
        ];
    }

    /**
     * Parse while loop {while condition}...{/while}
     * 
     * @throws SyntaxError
     */
    private function parseWhileLoop(): void
    {
        $this->pos += 6; // skip {while
        $this->skipWhitespace();

        $condition = $this->parseCondition();
        $this->expect('}');

        $body = [];

        while ($this->pos < strlen($this->source)) {
            if ($this->match('{/while')) {
                $this->pos += 7;
                $this->expect('}');
                break;
            }

            $this->parseNext();
            $body[] = end($this->nodes);
        }

        $this->nodes[] = [
            'type' => 'while',
            'condition' => $condition,
            'body' => $body,
        ];
    }

    /**
     * Parse for loop {for $i = 0; $i < 10; $i++}...{/for}
     * 
     * @throws SyntaxError
     */
    private function parseForLoop(): void
    {
        $this->pos += 4; // skip {for
        $this->skipWhitespace();

        // Parse for loop (simplified - just capture raw expression)
        $forExpr = '';
        while ($this->peek() !== '}') {
            $forExpr .= $this->peek();
            $this->pos++;
        }
        $this->expect('}');

        $body = [];

        while ($this->pos < strlen($this->source)) {
            if ($this->match('{/for')) {
                $this->pos += 5;
                $this->expect('}');
                break;
            }

            $this->parseNext();
            $body[] = end($this->nodes);
        }

        $this->nodes[] = [
            'type' => 'for',
            'expr' => trim($forExpr),
            'body' => $body,
        ];
    }

    /**
     * Parse block definition {block name}...{/block}
     * 
     * @throws SyntaxError
     */
    private function parseBlock(): void
    {
        $this->pos += 6; // skip {block
        $this->skipWhitespace();

        $name = $this->parseIdentifier();
        $this->skipWhitespace();
        $this->expect('}');

        $body = [];

        while ($this->pos < strlen($this->source)) {
            if ($this->match('{/block')) {
                $this->pos += 7;
                $this->expect('}');
                break;
            }

            $this->parseNext();
            $body[] = end($this->nodes);
        }

        $this->nodes[] = [
            'type' => 'block',
            'name' => $name,
            'body' => $body,
        ];
    }

    /**
     * Parse layout extension {extends 'layout.html'}
     * 
     * @throws SyntaxError
     */
    private function parseExtends(): void
    {
        $this->pos += 8; // skip {extends
        $this->skipWhitespace();

        $path = $this->parseString();
        $this->skipWhitespace();
        $this->expect('}');

        $this->nodes[] = [
            'type' => 'extends',
            'path' => $path,
        ];
    }

    /**
     * Parse include {include 'partial.html'} or {include 'partial', ['var' => 'value']}
     * 
     * @throws SyntaxError
     */
    private function parseInclude(): void
    {
        $this->pos += 8; // skip {include
        $this->skipWhitespace();

        $path = $this->parseString();
        $vars = [];

        // Optional variables
        if ($this->peek() === ',') {
            $this->pos++; // skip comma
            $this->skipWhitespace();
            // Parse array (simplified - just capture text)
            $arrayText = '';
            $bracketCount = 0;
            while ($this->peek() !== '}' || $bracketCount > 0) {
                $char = $this->peek();
                if ($char === '[') $bracketCount++;
                if ($char === ']') $bracketCount--;
                $arrayText .= $char;
                $this->pos++;
            }
            $vars = $arrayText;
        }

        $this->skipWhitespace();
        $this->expect('}');

        $this->nodes[] = [
            'type' => 'include',
            'path' => $path,
            'vars' => $vars,
        ];
    }

    /**
     * Parse plain text (everything not in braces)
     */
    private function parsePlainText(): void
    {
        $text = '';

        while ($this->pos < strlen($this->source)) {
            if ($this->peek() === '{') { 
                break;
            }

            if ($this->peek() === "\n") {
                $this->line++;
            }

            $text .= $this->peek();
            $this->pos++;
        }

        if ($text) {
            $this->nodes[] = [
                'type' => 'text',
                'content' => $text,
            ];
        }
    }

    /**
     * Parse expression (generic)
     */
    private function parseExpression(): array
    {
        $expr = '';
        while ($this->pos < strlen($this->source) && $this->peek() !== '}') {
            $expr .= $this->peek();
            $this->pos++;
        }
        
        if ($this->peek() !== '}') {
            throw new SyntaxError("Expected } at line {$this->line}");
        }
        $this->pos++;
        
        return [
            'type' => 'expression',
            'expr' => trim($expr),
            'line' => $this->line,
        ];
    }

    /**
     * Parse variable $var or $obj->prop or $arr['key']
     * 
     * @return string Variable expression
     * @throws SyntaxError
     */
    private function parseVariable(): string
    {
        if ($this->peek() !== '$') {
            throw new SyntaxError("Expected variable at line {$this->line}");
        }

        $var = '';
        while ($this->pos < strlen($this->source)) {
            $char = $this->peek();

            if (ctype_alnum($char) || $char === '_' || $char === '>' || $char === '[' || $char === ']' || $char === "'" || $char === '"' || $char === '$' || $char === '-') {
                $var .= $char;
                $this->pos++;
            } else {
                break;
            }
        }

        return $var;
    }

    /**
     * Parse condition
     */
    private function parseCondition(): string
    {
        $cond = '';

        while ($this->pos < strlen($this->source) && $this->peek() !== '}') {
            $cond .= $this->peek();
            $this->pos++;
        }

        return trim($cond);
    }

    /**
     * Parse string 'text' or "text"
     * 
     * @return string String content
     * @throws SyntaxError
     */
    private function parseString(): string
    {
        $quote = $this->peek();

        if ($quote !== '"' && $quote !== "'") {
            throw new SyntaxError("Expected string at line {$this->line}");
        }

        $this->pos++;
        $str = '';

        while ($this->pos < strlen($this->source)) {
            if ($this->peek() === $quote) {
                $this->pos++;
                return $str;
            }

            if ($this->peek() === '\\') {
                $this->pos++;
                $str .= $this->peek();
                $this->pos++;
            } else {
                $str .= $this->peek();
                $this->pos++;
            }
        }

        throw new SyntaxError("Unclosed string at line {$this->line}");
    }

    /**
     * Parse identifier
     * 
     * @return string Identifier
     */
    private function parseIdentifier(): string
    {
        $ident = '';

        while ($this->pos < strlen($this->source) && (ctype_alnum($this->peek()) || $this->peek() === '_')) {
            $ident .= $this->peek();
            $this->pos++;
        }

        return $ident;
    }

    /**
     * Check if current position matches string
     * 
     * @param string $str String to match
     * @return bool
     */
    private function match(string $str): bool
    {
        return strpos($this->source, $str, $this->pos) === $this->pos;
    }

    /**
     * Get character at current or next position
     * 
     * @param int $offset Offset from current position
     * @return string Character or empty string if at end
     */
    private function peek(int $offset = 0): string
    {
        $pos = $this->pos + $offset;
        return $pos < strlen($this->source) ? $this->source[$pos] : '';
    }

    /**
     * Expect specific character and advance
     * 
     * @param string $char Expected character
     * @throws SyntaxError
     */
    private function expect(string $char): void
    {
        if ($this->peek() !== $char) {
            throw new SyntaxError("Expected '{$char}' at line {$this->line}");
        }
        $this->pos++;
    }

    /**
     * Skip whitespace
     */
    private function skipWhitespace(): void
    {
        while ($this->pos < strlen($this->source) && ctype_space($this->peek())) {
            if ($this->peek() === "\n") $this->line++;
            $this->pos++;
        }
    }
}
