<?php

namespace Footup\View;

/**
 * Loop Helper - Provides $loop variable functionality
 * 
 * Auto-generated properties:
 * - index: 0-based index
 * - iteration: 1-based iteration number
 * - count: Total items in loop
 * - first: Is first iteration
 * - last: Is last iteration
 * - depth: Nesting level
 * - parent: Parent loop object (if nested)
 * - odd: Is odd iteration
 * - even: Is even iteration
 * - revindex: Reverse index (count - index - 1)
 * - reviteration: Reverse iteration (count - iteration + 1)
 */
class LoopHelper
{
    /**
     * @var int Loop counter (incremented for each loop)
     */
    private static int $loopCounter = 0;

    /**
     * @var object[] Loop stack
     */
    private static array $loopStack = [];

    /**
     * Create a loop object
     * 
     * @param int $index Current index
     * @param int $total Total items
     * @param object|null $parent Parent loop (for nesting)
     * @return object Loop object
     */
    public static function create(int $index, int $total, ?object $parent = null): object
    {
        $iteration = $index + 1;

        return (object) [
            'index' => $index,
            'iteration' => $iteration,
            'count' => $total,
            'first' => $index === 0,
            'last' => $index === $total - 1,
            'odd' => ($iteration % 2) === 1,
            'even' => ($iteration % 2) === 0,
            'depth' => count(self::$loopStack) + 1,
            'revindex' => $total - $index - 1,
            'reviteration' => $total - $iteration + 1,
            'parent' => $parent,
            'counter' => self::$loopCounter++,
        ];
    }

    /**
     * Push loop to stack
     * 
     * @param object $loop Loop object
     */
    public static function push(object $loop): void
    {
        self::$loopStack[] = $loop;
    }

    /**
     * Pop loop from stack
     * 
     * @return object|null
     */
    public static function pop(): ?object
    {
        return array_pop(self::$loopStack) ?? null;
    }

    /**
     * Get current loop
     * 
     * @return object|null
     */
    public static function current(): ?object
    {
        return end(self::$loopStack) ?: null;
    }

    /**
     * Get loop depth
     * 
     * @return int
     */
    public static function getDepth(): int
    {
        return count(self::$loopStack);
    }

    /**
     * Get 0-based index
     * 
     * @param array $loops Loop stack
     * @return int
     */
    public static function getIndex(array $loops): int
    {
        return count($loops) - 1;
    }

    /**
     * Get 1-based iteration number
     * 
     * @param array $loops Loop stack
     * @return int
     */
    public static function getIteration(array $loops): int
    {
        return count($loops);
    }

    /**
     * Check if first iteration
     * 
     * @param array $loops Loop stack
     * @return bool
     */
    public static function isFirst(array $loops): bool
    {
        return count($loops) === 1;
    }

    /**
     * Check if last iteration
     * 
     * @param array $loops Loop stack
     * @param int $total Total items
     * @return bool
     */
    public static function isLast(array $loops, int $total): bool
    {
        return static::getIndex($loops) === $total - 1;
    }

    /**
     * Reset loop helper
     */
    public static function reset(): void
    {
        self::$loopStack = [];
        self::$loopCounter = 0;
    }
}
