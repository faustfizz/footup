<?php

declare(strict_types=1);

namespace Footup\Utils;

/*
|--------------------------------------------------------------------------
| Collection — fluent array manipulation
|
| Usage:
|   collect($users)->map(fn($u) => $u->name)->filter(...)->toArray()
|   collect([1,2,3])->sum()
|   collect($posts)->sortByDesc('created_at')->take(5)->pluck('title')
|--------------------------------------------------------------------------
*/

class Collection
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    /**
     * Map over items
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * Filter items
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->items, $callback));
    }

    /**
     * Get unique items
     */
    public function unique(callable $callback = null): self
    {
        if ($callback === null) {
            return new self(array_unique($this->items));
        }

        $seen = [];
        $result = [];

        foreach ($this->items as $item) {
            $key = $callback($item);
            if (!in_array($key, $seen, true)) {
                $seen[] = $key;
                $result[] = $item;
            }
        }

        return new self($result);
    }

    /**
     * Sort items
     */
    public function sort(callable $callback = null): self
    {
        $items = $this->items;

        if ($callback === null) {
            sort($items);
        } else {
            usort($items, $callback);
        }

        return new self($items);
    }

    /**
     * Sort by key descending
     */
    public function sortByDesc(string $key): self
    {
        $items = $this->items;

        usort($items, function ($a, $b) use ($key) {
            $aVal = is_object($a) ? $a->$key : $a[$key];
            $bVal = is_object($b) ? $b->$key : $b[$key];

            return $bVal <=> $aVal;
        });

        return new self($items);
    }

    /**
     * Sort by key ascending
     */
    public function sortBy(string $key): self
    {
        $items = $this->items;

        usort($items, function ($a, $b) use ($key) {
            $aVal = is_object($a) ? $a->$key : $a[$key];
            $bVal = is_object($b) ? $b->$key : $b[$key];

            return $aVal <=> $bVal;
        });

        return new self($items);
    }

    /**
     * Take first N items
     */
    public function take(int $n): self
    {
        return new self(array_slice($this->items, 0, $n));
    }

    /**
     * Skip first N items
     */
    public function skip(int $n): self
    {
        return new self(array_slice($this->items, $n));
    }

    /**
     * Get value at key from all items
     */
    public function pluck(string $key): self
    {
        return $this->map(function ($item) use ($key) {
            return is_object($item) ? $item->$key : $item[$key];
        });
    }

    /**
     * Get first item
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get last item
     */
    public function last(): mixed
    {
        return end($this->items) ?: null;
    }

    /**
     * Join items as string
     */
    public function implode(string $glue): string
    {
        return implode($glue, $this->items);
    }

    /**
     * Sum all items
     */
    public function sum(callable $callback = null): int|float
    {
        if ($callback === null) {
            return array_sum($this->items);
        }

        return array_sum(array_map($callback, $this->items));
    }

    /**
     * Count items
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if item exists
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * Get all items
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->items);
    }

    /**
     * Reverse items
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->items));
    }

    /**
     * Chunk into groups
     */
    public function chunk(int $size): self
    {
        return new self(array_chunk($this->items, $size));
    }

    /**
     * Merge with another collection
     */
    public function merge(Collection $other): self
    {
        return new self(array_merge($this->items, $other->toArray()));
    }

    /**
     * Group by key
     */
    public function groupBy(callable $callback): array
    {
        $groups = [];

        foreach ($this->items as $item) {
            $key = $callback($item);
            $groups[$key][] = $item;
        }

        return $groups;
    }
}
