<?php

namespace Footup\View;

use Footup\Exceptions\SyntaxError;

/**
 * Attribute Parser - Parse and compile HTML attributes
 * 
 * Supports two prefixes: ui: and :
 * 
 * Examples:
 * - ui:if="$condition"
 * - :foreach="$items as $item"
 * - ui:tag-if="$item->href"
 * - :class="{active: $active, disabled: $disabled}"
 */
class AttributeParser
{
    /**
     * @var array Supported attribute prefixes
     */
    private array $prefixes = ['ui:', ':'];

    /**
     * @var array Parsed attributes
     */
    private array $attributes = [];

    /**
     * @var string Source HTML
     */
    private string $source = '';

    /**
     * @var int Current position
     */
    private int $pos = 0;

    /**
     * Constructor
     * 
     * @param array $prefixes Attribute prefixes to support
     */
    public function __construct(array $prefixes = ['ui:', ':'])
    {
        $this->prefixes = $prefixes;
    }

    /**
     * Parse HTML element and extract template attributes
     * 
     * @param string $htmlElement HTML element (e.g., '<div ui:if="...">...</div>')
     * @return array Parsed attribute directives
     * @throws SyntaxError
     */
    public function parseElement(string $htmlElement): array
    {
        $this->source = $htmlElement;
        $this->pos = 0;
        $this->attributes = [];

        // Find opening tag
        if (!preg_match('/<(\w+)([^>]*)>/', $htmlElement, $matches)) {
            return [];
        }

        $tagName = $matches[1];
        $attrString = $matches[2];

        // Parse all attributes
        $this->parseAttributes($attrString);

        return [
            'tag' => $tagName,
            'directives' => $this->attributes,
        ];
    }

    /**
     * Parse attribute string
     * 
     * @param string $attrString Attribute string from opening tag
     * @throws SyntaxError
     */
    private function parseAttributes(string $attrString): void
    {
        $this->source = $attrString;
        $this->pos = 0;

        while ($this->pos < strlen($this->source)) {
            $this->skipWhitespace();

            if ($this->pos >= strlen($this->source)) {
                break;
            }

            // Check for directive attribute
            $directive = $this->parseDirectiveAttribute();

            if ($directive) {
                $this->attributes[] = $directive;
            } else {
                // Skip regular HTML attribute
                $this->skipAttribute();
            }
        }
    }

    /**
     * Parse a directive attribute (ui:* or :*)
     * 
     * @return array|null Parsed directive or null if not a directive
     * @throws SyntaxError
     */
    private function parseDirectiveAttribute(): ?array
    {
        $startPos = $this->pos;

        // Check if starts with a directive prefix
        $prefix = null;
        foreach ($this->prefixes as $p) {
            if ($this->match($p)) {
                $prefix = $p;
                break;
            }
        }

        if (!$prefix) {
            return null;
        }

        $this->pos += strlen($prefix);

        // Parse directive name (e.g., "if", "foreach", "tag-if")
        $name = $this->parseIdentifier();

        if (!$name) {
            throw new SyntaxError("Expected directive name after '{$prefix}'");
        }

        $this->skipWhitespace();

        // Expect =
        if ($this->peek() !== '=') {
            throw new SyntaxError("Expected '=' after directive '{$prefix}{$name}'");
        }
        $this->pos++;

        $this->skipWhitespace();

        // Parse value (quoted string)
        $value = $this->parseQuotedValue();

        return [
            'prefix' => $prefix,
            'name' => $name,
            'value' => $value,
        ];
    }

    /**
     * Parse a regular HTML attribute (skip it)
     */
    private function skipAttribute(): void
    {
        // Parse attribute name
        while ($this->pos < strlen($this->source) && 
               (ctype_alnum($this->peek()) || $this->peek() === '-' || $this->peek() === '_')) {
            $this->pos++;
        }

        $this->skipWhitespace();

        // If has value, skip it
        if ($this->peek() === '=') {
            $this->pos++;
            $this->skipWhitespace();
            $this->parseQuotedValue();
        }
    }

    /**
     * Parse quoted string value
     * 
     * @return string Unquoted value
     * @throws SyntaxError
     */
    private function parseQuotedValue(): string
    {
        $quote = $this->peek();

        if ($quote !== '"' && $quote !== "'") {
            throw new SyntaxError("Expected quoted value");
        }

        $this->pos++;
        $value = '';

        while ($this->pos < strlen($this->source)) {
            $char = $this->peek();

            if ($char === $quote) {
                $this->pos++;
                return $value;
            }

            if ($char === '\\') {
                $this->pos++;
                $value .= $this->peek();
                $this->pos++;
            } else {
                $value .= $char;
                $this->pos++;
            }
        }

        throw new SyntaxError("Unclosed quoted value");
    }

    /**
     * Parse identifier
     * 
     * @return string
     */
    private function parseIdentifier(): string
    {
        $ident = '';

        while ($this->pos < strlen($this->source)) {
            $char = $this->peek();

            if (ctype_alnum($char) || $char === '-' || $char === '_') {
                $ident .= $char;
                $this->pos++;
            } else {
                break;
            }
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
     * Get character at current position
     * 
     * @return string
     */
    private function peek(): string
    {
        return $this->pos < strlen($this->source) ? $this->source[$this->pos] : '';
    }

    /**
     * Skip whitespace
     */
    private function skipWhitespace(): void
    {
        while ($this->pos < strlen($this->source) && ctype_space($this->peek())) {
            $this->pos++;
        }
    }

    /**
     * Compile directive to PHP code
     * 
     * @param array $directive Parsed directive
     * @param string $innerHtml Inner HTML content
     * @param string $tagName Tag name
     * @return string Compiled PHP/HTML
     * @throws SyntaxError
     */
    public function compileDirective(array $directive, string $innerHtml, string $tagName): string
    {
        $name = $directive['name'];
        $value = $directive['value'];

        return match ($name) {
            'if' => $this->compileIfDirective($value, $innerHtml, $tagName),
            'foreach' => $this->compileForeachDirective($value, $innerHtml, $tagName),
            'tag-if' => $this->compileTagIfDirective($value, $innerHtml, $tagName),
            'tag' => $this->compileTagDirective($value, $innerHtml, $tagName),
            'class' => $this->compileClassDirective($value, $innerHtml, $tagName),
            'include' => $this->compileIncludeDirective($value, $tagName),
            'else' => $this->compileElseDirective($innerHtml, $tagName),
            default => throw new SyntaxError("Unknown directive: {$name}"),
        };
    }

    /**
     * Compile ui:if / :if directive
     * 
     * {if condition}
     */
    private function compileIfDirective(string $condition, string $innerHtml, string $tagName): string
    {
        return "<?php if ({$condition}): ?>\n{$innerHtml}\n<?php endif; ?>";
    }

    /**
     * Compile ui:foreach / :foreach directive
     * 
     * {foreach $array as $item}
     */
    private function compileForeachDirective(string $expression, string $innerHtml, string $tagName): string
    {
        // Parse: $array as $item or $array as $key => $value
        if (!preg_match('/\$(\w+)\s+as\s+(?:\$(\w+)\s*=>\s*)?\$(\w+)/', $expression, $matches)) {
            throw new SyntaxError("Invalid foreach expression: {$expression}");
        }

        $array = '$' . $matches[1];
        $key = isset($matches[2]) && $matches[2] ? '$' . $matches[2] : null;
        $value = '$' . (isset($matches[2]) && $matches[2] ? $matches[3] : $matches[2]);

        $foreachCode = "foreach ({$array} as ";
        if ($key) {
            $foreachCode .= "{$key} => {$value}";
        } else {
            $foreachCode .= "{$value}";
        }
        $foreachCode .= ")";

        return "<?php {$foreachCode}: ?>\n{$innerHtml}\n<?php endforeach; ?>";
    }

    /**
     * Compile ui:tag-if / :tag-if directive
     * 
     * Renders tag if condition true, renders content without tag if false
     */
    private function compileTagIfDirective(string $condition, string $innerHtml, string $tagName): string
    {
        // Extract attributes and content from innerHtml
        $pattern = '/<' . preg_quote($tagName) . '([^>]*)>(.*)<\/' . preg_quote($tagName) . '>/s';
        
        if (!preg_match($pattern, $innerHtml, $matches)) {
            return $innerHtml;
        }

        $attrs = $matches[1];
        $content = $matches[2];

        // Remove directive attribute from attrs
        $attrs = preg_replace('/\s*(ui:tag-if|:tag-if)="[^"]*"/', '', $attrs);

        $code = "<?php if ({$condition}): ?>\n";
        $code .= "<{$tagName}{$attrs}>{$content}</{$tagName}>\n";
        $code .= "<?php else: ?>\n";
        $code .= "{$content}\n";
        $code .= "<?php endif; ?>";

        return $code;
    }

    /**
     * Compile ui:tag / :tag directive
     * 
     * Dynamic tag name from expression
     */
    private function compileTagDirective(string $expression, string $innerHtml, string $tagName): string
    {
        // Extract content and attributes
        $pattern = '/<' . preg_quote($tagName) . '([^>]*)>(.*)<\/' . preg_quote($tagName) . '>/s';
        
        if (!preg_match($pattern, $innerHtml, $matches)) {
            return $innerHtml;
        }

        $attrs = $matches[1];
        $content = $matches[2];

        // Remove directive attribute from attrs
        $attrs = preg_replace('/\s*(ui:tag|:tag)="[^"]*"/', '', $attrs);

        return "<?php \$__tag = {$expression}; ?>\n" .
               "<<?php echo \$__tag; ?>{$attrs}>{$content}</<?php echo \$__tag; ?>>\n";
    }

    /**
     * Compile ui:class / :class directive
     * 
     * Dynamic class binding: {active: $active, disabled: $disabled}
     */
    private function compileClassDirective(string $expression, string $innerHtml, string $tagName): string
    {
        // Parse class expression
        // Simple format: $variable or {className: $condition, ...}
        
        if (str_starts_with($expression, '{')) {
            // Object literal syntax
            $classes = $this->parseClassObject($expression);
            $classCode = $this->generateClassCode($classes);
        } else {
            // Simple variable
            $classCode = "echo {$expression};";
        }

        // Update innerHtml to use computed classes
        $pattern = '/class="([^"]*)"/' ;
        $replacement = 'class="<?php ' . $classCode . ' ?>"';
        
        return preg_replace($pattern, $replacement, $innerHtml);
    }

    /**
     * Parse class object literal {class1: $cond1, class2: $cond2}
     * 
     * @param string $classExpr Class object expression
     * @return array Classes with conditions
     */
    private function parseClassObject(string $classExpr): array
    {
        $classes = [];

        // Remove braces
        $inner = trim($classExpr, '{}');

        // Split by comma (simple approach)
        $pairs = explode(',', $inner);

        foreach ($pairs as $pair) {
            if (strpos($pair, ':') === false) {
                continue;
            }

            [$className, $condition] = explode(':', $pair, 2);
            $classes[trim($className, " '\"")] = trim($condition);
        }

        return $classes;
    }

    /**
     * Generate PHP code for class binding
     * 
     * @param array $classes Classes with conditions
     * @return string PHP code
     */
    private function generateClassCode(array $classes): string
    {
        $code = '\$__classes = []; ';

        foreach ($classes as $className => $condition) {
            $code .= "if ({$condition}) \$__classes[] = '{$className}'; ";
        }

        $code .= 'echo implode(\' \', \$__classes);';

        return $code;
    }

    /**
     * Compile ui:include / :include directive
     */
    private function compileIncludeDirective(string $path, string $tagName): string
    {
        $path = trim($path, '\'"');
        return "<?php include \$__engine->find('{$path}'); ?>";
    }

    /**
     * Compile ui:else / :else directive
     */
    private function compileElseDirective(string $innerHtml, string $tagName): string
    {
        return "<?php else: ?>\n{$innerHtml}\n<?php endif; ?>";
    }

    /**
     * Get supported prefixes
     * 
     * @return array
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }

    /**
     * Check if attribute is a directive
     * 
     * @param string $attrName Attribute name
     * @return bool
     */
    public function isDirective(string $attrName): bool
    {
        foreach ($this->prefixes as $prefix) {
            if (str_starts_with($attrName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract directive name from attribute
     * 
     * @param string $attrName Attribute name (e.g., "ui:if", ":if")
     * @return string Directive name (e.g., "if")
     */
    public function extractDirectiveName(string $attrName): string
    {
        foreach ($this->prefixes as $prefix) {
            if (str_starts_with($attrName, $prefix)) {
                return substr($attrName, strlen($prefix));
            }
        }

        return $attrName;
    }
}
