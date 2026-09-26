<?php

namespace Footup\View;

/**
 * Filter Registry - Manages template filters
 * 
 * Built-in filters:
 * - upper: Uppercase
 * - lower: Lowercase
 * - trim: Trim whitespace
 * - escape: HTML escape
 * - raw: No escape (same as noescape)
 * - noescape: No escape (same as raw)
 * - date: Format date
 * - number: Format number with decimals
 * - truncate: Truncate to length
 * - replace: Replace string
 * - count: Count array/string
 * - base64: Base64 encode
 * - url: URL escape
 * - attr: Attribute escape
 * - json: JSON encode
 * - css: CSS escape
 */
class FilterRegistry
{
    /**
     * @var callable[] Registered filters
     */
    private array $filters = [];

    /**
     * Constructor - register built-in filters
     */
    public function __construct()
    {
        $this->registerBuiltInFilters();
    }

    /**
     * Register built-in filters
     */
    private function registerBuiltInFilters(): void
    {
        // Text filters
        $this->register('upper', fn($v) => strtoupper($v));
        $this->register('lower', fn($v) => strtolower($v));
        $this->register('trim', fn($v) => trim($v));
        $this->register('escape', fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
        
        // No-escape filters (just return value as-is)
        $this->register('raw', fn($v) => $v);
        $this->register('noescape', fn($v) => $v);

        // Date filter
        $this->register('date', function($value, $format = 'Y-m-d H:i:s') {
            if ($value instanceof \DateTime) {
                return $value->format($format);
            }
            if (is_numeric($value)) {
                return date($format, $value);
            }
            if (is_string($value)) {
                return date($format, strtotime($value));
            }
            return $value;
        });

        // Number filter
        $this->register('number', function($value, $decimals = 0) {
            return number_format((float)$value, (int)$decimals);
        });

        // Truncate filter
        $this->register('truncate', function($value, $length = 50, $suffix = '...') {
            if (strlen($value) <= $length) {
                return $value;
            }
            return substr($value, 0, $length) . $suffix;
        });

        // Replace filter
        $this->register('replace', function($value, $search, $replace) {
            return str_replace($search, $replace, $value);
        });

        // Count filter
        $this->register('count', fn($v) => is_countable($v) ? count($v) : 0);

        // Base64 filter
        $this->register('base64', fn($v) => base64_encode($v));
        $this->register('base64_decode', fn($v) => base64_decode($v));

        // URL escape
        $this->register('url', fn($v) => rawurlencode($v));

        // Attribute escape
        $this->register('attr', fn($v) => htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // JSON encode
        $this->register('json', fn($v) => json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // CSS escape
        $this->register('css', function($value) {
            return preg_replace_callback('/[^a-z0-9]/i', function($matches) {
                return '\\' . dechex(ord($matches[0]));
            }, $value);
        });

        // Reverse filter
        $this->register('reverse', function($value) {
            if (is_array($value)) {
                return array_reverse($value);
            }
            return strrev($value);
        });

        // Keys filter
        $this->register('keys', fn($v) => is_array($v) ? array_keys($v) : []);

        // Values filter
        $this->register('values', fn($v) => is_array($v) ? array_values($v) : []);

        // Implode filter
        $this->register('join', function($value, $separator = ', ') {
            if (is_array($value)) {
                return implode($separator, $value);
            }
            return $value;
        });

        // Explode filter (as 'split')
        $this->register('split', function($value, $separator = ' ') {
            if (is_string($value)) {
                return explode($separator, $value);
            }
            return $value;
        });

        // Default filter
        $this->register('default', function($value, $default = '') {
            return empty($value) ? $default : $value;
        });

        // Abs filter
        $this->register('abs', fn($v) => abs($v));

        // Min filter
        $this->register('min', fn($v) => is_array($v) ? min($v) : $v);

        // Max filter
        $this->register('max', fn($v) => is_array($v) ? max($v) : $v);

        // Sum filter
        $this->register('sum', fn($v) => is_array($v) ? array_sum($v) : $v);

        // First filter
        $this->register('first', function($value) {
            if (is_array($value)) {
                return reset($value);
            }
            return $value[0] ?? null;
        });

        // Last filter
        $this->register('last', function($value) {
            if (is_array($value)) {
                return end($value);
            }
            return $value[strlen($value) - 1] ?? null;
        });
    }

    /**
     * Register a filter
     * 
     * @param string $name Filter name
     * @param callable $callback Filter function
     */
    public function register(string $name, callable $callback): void
    {
        $this->filters[$name] = $callback;
    }

    /**
     * Get a registered filter
     * 
     * @param string $name Filter name
     * @return callable|null Filter function or null
     */
    public function get(string $name): ?callable
    {
        return $this->filters[$name] ?? null;
    }

    /**
     * Apply a filter (static method for use in compiled templates)
     * 
     * @param string $name Filter name
     * @param mixed $value Value to filter
     * @param array $args Additional arguments
     * @return mixed Filtered value
     */
    public static function apply(string $name, $value, array $args = [])
    {
        // This would need access to registry - for now return value
        // The compiler should inline this logic
        return $value;
    }

    /**
     * Check if filter exists
     * 
     * @param string $name Filter name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    /**
     * Get all filters
     * 
     * @return array Filter names
     */
    public function all(): array
    {
        return array_keys($this->filters);
    }
}
