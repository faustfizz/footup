<?php

namespace Footup\View;

/**
 * Template Renderer - Orchestrates template processing
 * 
 * Combines:
 * - Parser (Latte syntax)
 * - AttributeParser (HTML attributes)
 * - Compiler (AST to PHP)
 */
class Renderer
{
    /**
     * @var Parser Template parser
     */
    private Parser $parser;

    /**
     * @var AttributeParser Attribute parser
     */
    private AttributeParser $attrParser;

    /**
     * @var Compiler Template compiler
     */
    private Compiler $compiler;

    /**
     * @var FilterRegistry Filter registry
     */
    private FilterRegistry $filters;

    /**
     * @var AttributeRegistry Attribute registry
     */
    private AttributeRegistry $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = new Parser();
        $this->attrParser = new AttributeParser(['ui:', ':']);
        $this->compiler = new Compiler();
        $this->filters = new FilterRegistry();
        $this->attributes = new AttributeRegistry();
    }

    /**
     * Process complete template source
     * 
     * 1. Parse Latte syntax to AST
     * 2. Extract and compile HTML attributes
     * 3. Compile AST to PHP
     * 
     * @param string $source Template source
     * @return string Compiled PHP code
     */
    public function render(string $source): string
    {
        // Step 1: Process attributes in HTML
        $processed = $this->processAttributes($source);

        // Step 2: Parse Latte syntax
        $ast = $this->parser->parse($processed, [
            'attributePrefixes' => ['ui:', ':'],
            'filters' => $this->filters,
            'attributes' => $this->attributes,
        ]);

        // Step 3: Compile to PHP
        $code = $this->compiler->compile($ast, [
            'filters' => $this->filters,
            'attributes' => $this->attributes,
        ]);

        return $code;
    }

    /**
     * Process HTML attributes (ui:* and :*)
     * 
     * Converts:
     *   <div ui:if="$active">...</div>
     * To:
     *   <?php if ($active): ?>..<?php endif; ?>
     * 
     * @param string $source Template source
     * @return string Processed source
     */
    private function processAttributes(string $source): string
    {
        // Find all HTML tags
        $pattern = '/<(\w+)([^>]*)>(.*?)<\/\1>/s';

        return preg_replace_callback($pattern, function($matches) {
            $tagName = $matches[1];
            $attrsStr = $matches[2];
            $innerHtml = $matches[3];

            // Parse attributes
            $parsed = $this->attrParser->parseElement("<{$tagName}{$attrsStr}>");

            if (empty($parsed['directives'])) {
                // No directives, return as-is
                return $matches[0];
            }

            // Process directives
            $result = $this->processDirectives($parsed['directives'], $innerHtml, $tagName, $attrsStr);

            return $result;
        }, $source);
    }

    /**
     * Process directives for an element
     * 
     * @param array $directives Parsed directives
     * @param string $innerHtml Inner HTML content
     * @param string $tagName Tag name
     * @param string $attrsStr Original attribute string
     * @return string Processed HTML/PHP
     */
    private function processDirectives(array $directives, string $innerHtml, string $tagName, string $attrsStr): string
    {
        // Handle multiple directives
        if (empty($directives)) {
            return "<{$tagName}{$attrsStr}>{$innerHtml}</{$tagName}>";
        }

        $result = $innerHtml;
        $attrsStr = preg_replace('/\s*(ui:[a-z-]+|:[a-z-]+)="[^"]*"/', '', $attrsStr);

        foreach ($directives as $directive) {
            $name = $directive['name'];
            $value = $directive['value'];

            switch ($name) {
                case 'if':
                    $result = "<?php if ({$value}): ?>" .
                             "<{$tagName}{$attrsStr}>{$result}</{$tagName}>" .
                             "<?php endif; ?>";
                    break;

                case 'foreach':
                    $result = "<?php {$this->buildForeachStatement($value)}: ?>" .
                             "<{$tagName}{$attrsStr}>{$result}</{$tagName}>" .
                             "<?php endforeach; ?>";
                    break;

                case 'tag-if':
                    $result = "<?php if ({$value}): ?>" .
                             "<{$tagName}{$attrsStr}>{$result}</{$tagName}>" .
                             "<?php else: ?>" .
                             "{$result}" .
                             "<?php endif; ?>";
                    break;

                case 'tag':
                    $result = "<?php \$__tag = {$value}; ?>" .
                             "<<?php echo \$__tag; ?>{$attrsStr}>{$result}</<?php echo \$__tag; ?>>";
                    break;

                case 'class':
                    $result = $this->applyClassDirective($value, $result, $tagName, $attrsStr);
                    break;

                case 'include':
                    $result = "<?php include \$__engine->find('" . trim($value, '\'"') . "'); ?>";
                    break;
            }
        }

        return $result;
    }

    /**
     * Build foreach statement
     * 
     * @param string $expression Foreach expression
     * @return string PHP foreach statement
     */
    private function buildForeachStatement(string $expression): string
    {
        if (preg_match('/\$(\w+)\s+as\s+(?:\$(\w+)\s*=>\s*)?\$(\w+)/', $expression, $m)) {
            $array = '$' . $m[1];
            $key = isset($m[2]) && $m[2] ? '$' . $m[2] : null;
            $value = '$' . (isset($m[2]) && $m[2] ? $m[3] : $m[2]);

            $stmt = "foreach ({$array} as ";
            if ($key) {
                $stmt .= "{$key} => {$value}";
            } else {
                $stmt .= "{$value}";
            }
            $stmt .= ")";

            return $stmt;
        }

        return "foreach ({$expression})";
    }

    /**
     * Apply class directive
     * 
     * @param string $classExpr Class expression
     * @param string $html Element HTML
     * @param string $tagName Tag name
     * @param string $attrsStr Attribute string
     * @return string Processed HTML
     */
    private function applyClassDirective(string $classExpr, string $html, string $tagName, string $attrsStr): string
    {
        // For now, append to class attribute
        // In a full implementation, would parse object literal

        if (preg_match('/class="([^"]*)"/', $attrsStr, $m)) {
            $currentClass = $m[1];
            $newClass = $currentClass . " <?php echo {$classExpr}; ?>";
            $attrsStr = str_replace('class="' . $currentClass . '"', 'class="' . $newClass . '"', $attrsStr);
        } else {
            $attrsStr .= ' class="<?php echo ' . $classExpr . '; ?>"';
        }

        return "<{$tagName}{$attrsStr}>{$html}</{$tagName}>";
    }

    /**
     * Get parser
     * 
     * @return Parser
     */
    public function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * Get attribute parser
     * 
     * @return AttributeParser
     */
    public function getAttributeParser(): AttributeParser
    {
        return $this->attrParser;
    }

    /**
     * Get compiler
     * 
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }

    /**
     * Get filters
     * 
     * @return FilterRegistry
     */
    public function getFilters(): FilterRegistry
    {
        return $this->filters;
    }

    /**
     * Get attributes
     * 
     * @return AttributeRegistry
     */
    public function getAttributes(): AttributeRegistry
    {
        return $this->attributes;
    }
}
