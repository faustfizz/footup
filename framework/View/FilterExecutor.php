<?php

namespace Footup\View;

/**
 * Filter Executor - Apply filters at runtime
 * 
 * Bridges FilterRegistry with compiled template execution
 */
class FilterExecutor
{
    /**
     * @var callable[] Registered filters
     */
    private static array $filters = [];

    /**
     * Initialize with registry
     * 
     * @param FilterRegistry $registry
     */
    public static function init(FilterRegistry $registry): void
    {
        // Copy filters from registry for static access
        foreach ($registry->all() as $name) {
            $filter = $registry->get($name);
            if ($filter) {
                self::$filters[$name] = $filter;
            }
        }
    }

    /**
     * Register a filter
     * 
     * @param string $name Filter name
     * @param callable $callback Filter function
     */
    public static function register(string $name, callable $callback): void
    {
        self::$filters[$name] = $callback;
    }

    /**
     * Apply a filter to a value
     * 
     * @param string $name Filter name
     * @param mixed $value Value to filter
     * @param array $args Filter arguments
     * @return mixed Filtered value
     */
    public static function apply(string $name, $value, array $args = [])
    {
        if (!isset(self::$filters[$name])) {
            return $value;
        }

        $callback = self::$filters[$name];

        try {
            if (empty($args)) {
                return $callback($value);
            }

            return $callback($value, ...$args);
        } catch (\Throwable $e) {
            // Return original value on error
            return $value;
        }
    }

    /**
     * Apply a chain of filters
     * 
     * @param mixed $value Initial value
     * @param array $filters Filter specifications
     * @return mixed Filtered value
     */
    public static function applyChain($value, array $filters)
    {
        foreach ($filters as $filter) {
            if (is_array($filter)) {
                $name = $filter['name'] ?? null;
                $args = $filter['args'] ?? [];
                if ($name) {
                    $value = self::apply($name, $value, $args);
                }
            }
        }

        return $value;
    }

    /**
     * Check if filter exists
     * 
     * @param string $name Filter name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$filters[$name]);
    }

    /**
     * Get all filters
     * 
     * @return array Filter names
     */
    public static function all(): array
    {
        return array_keys(self::$filters);
    }

    /**
     * Get filter count
     * 
     * @return int
     */
    public static function count(): int
    {
        return count(self::$filters);
    }
}
