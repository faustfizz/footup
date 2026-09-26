<?php

namespace Footup\View;

/**
 * Attribute Registry - Manages template attribute directives
 * 
 * Built-in attributes (support both ui: and : prefix):
 * - if: Conditional rendering
 * - foreach: Loop rendering
 * - tag-if: Conditional tag (a→span if condition)
 * - tag: Dynamic tag name
 * - class: Dynamic classes
 * - include: Include partial template
 * - else: Else clause for if
 */
class AttributeRegistry
{
    /**
     * @var callable[] Registered directives
     */
    private array $directives = [];

    /**
     * Constructor - register built-in directives
     */
    public function __construct()
    {
        $this->registerBuiltInDirectives();
    }

    /**
     * Register built-in attribute directives
     */
    private function registerBuiltInDirectives(): void
    {
        // Conditional rendering
        $this->register('if', function($condition, $element) {
            return [
                'type' => 'if',
                'condition' => $condition,
                'element' => $element,
            ];
        });

        // Loop rendering
        $this->register('foreach', function($expression, $element) {
            // Parse: $array as $item or $array as $key => $value
            preg_match('/\$(\w+)\s+as\s+(?:\$(\w+)\s*=>\s*)?\$(\w+)/', $expression, $matches);
            
            return [
                'type' => 'foreach',
                'array' => '$' . $matches[1],
                'key' => isset($matches[2]) && $matches[2] ? '$' . $matches[2] : null,
                'value' => '$' . (isset($matches[2]) && $matches[2] ? $matches[3] : $matches[2]),
                'element' => $element,
            ];
        });

        // Conditional tag
        $this->register('tag-if', function($condition, $element) {
            return [
                'type' => 'tag-if',
                'condition' => $condition,
                'element' => $element,
                'trueTag' => $element->tagName,
                'falseTag' => $this->getDefaultTag($element->tagName),
            ];
        });

        // Dynamic tag
        $this->register('tag', function($expression, $element) {
            return [
                'type' => 'tag',
                'expr' => $expression,
                'element' => $element,
            ];
        });

        // Dynamic classes
        $this->register('class', function($expression, $element) {
            return [
                'type' => 'class',
                'expr' => $expression,
                'element' => $element,
            ];
        });

        // Include partial
        $this->register('include', function($path, $element) {
            return [
                'type' => 'include',
                'path' => $path,
                'element' => $element,
            ];
        });

        // Else clause
        $this->register('else', function($condition, $element) {
            return [
                'type' => 'else',
                'element' => $element,
            ];
        });
    }

    /**
     * Get default tag for a given tag (for tag-if)
     * 
     * @param string $tag Original tag
     * @return string Default wrapper tag
     */
    private function getDefaultTag(string $tag): string
    {
        return match ($tag) {
            'a', 'button' => 'span',
            'img' => 'span',
            'input' => 'span',
            'select' => 'span',
            'textarea' => 'span',
            default => 'div',
        };
    }

    /**
     * Register a directive
     * 
     * @param string $name Directive name (without prefix)
     * @param callable $callback Directive handler
     */
    public function register(string $name, callable $callback): void
    {
        $this->directives[$name] = $callback;
    }

    /**
     * Get a registered directive
     * 
     * @param string $name Directive name
     * @return callable|null Directive handler or null
     */
    public function get(string $name): ?callable
    {
        return $this->directives[$name] ?? null;
    }

    /**
     * Check if directive exists
     * 
     * @param string $name Directive name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->directives[$name]);
    }

    /**
     * Get all directives
     * 
     * @return array Directive names
     */
    public function all(): array
    {
        return array_keys($this->directives);
    }

    /**
     * Parse attribute string (e.g., "condition" or "$array as $item")
     * 
     * @param string $directive Directive name
     * @param string $value Attribute value
     * @return array Parsed directive info
     */
    public function parse(string $directive, string $value): array
    {
        if ($directive === 'if') {
            return [
                'type' => 'if',
                'condition' => $value,
            ];
        } elseif ($directive === 'foreach') {
            // Parse: $array as $item or $array as $key => $value
            if (preg_match('/\$(\w+)\s+as\s+(?:\$(\w+)\s*=>\s*)?\$(\w+)/', $value, $m)) {
                return [
                    'type' => 'foreach',
                    'array' => '$' . $m[1],
                    'key' => isset($m[2]) && $m[2] ? '$' . $m[2] : null,
                    'value' => '$' . (isset($m[2]) && $m[2] ? $m[3] : $m[2]),
                ];
            }
        } elseif ($directive === 'tag-if') {
            return [
                'type' => 'tag-if',
                'condition' => $value,
            ];
        } elseif ($directive === 'tag') {
            return [
                'type' => 'tag',
                'expr' => $value,
            ];
        } elseif ($directive === 'class') {
            return [
                'type' => 'class',
                'expr' => $value,
            ];
        } elseif ($directive === 'include') {
            return [
                'type' => 'include',
                'path' => trim($value, '\'"'),
            ];
        } elseif ($directive === 'else') {
            return [
                'type' => 'else',
            ];
        }

        return [];
    }
}
