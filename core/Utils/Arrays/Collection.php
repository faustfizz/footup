<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils\Arrays
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Utils\Arrays;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->extractCollectibleItems($items);
    }

    /**
     * @param mixed $items
     * @return array
     */
    protected function extractCollectibleItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        return (array) $items;
    }

    /**
     * Return the underlining array.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Return a slice of the underlining array.
     *
     * @param int  $offset
     * @param int  $length
     * @param bool $preserveKeys
     *
     * @return array
     */
    public function slice($offset, $length, $preserveKeys = false)
    {
        return array_slice($this->items, $offset, $length, $preserveKeys);
    }

    /**
     * Get the first item in the collection.
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Get the nth item in the collection.
     * @param int $position
     * @return mixed
     */
    public function nth($position)
    {
        if ($position < 0) {
            $position = $this->count() + $position;
        }
        if ($position < 0 || $this->count() < $position) {
            throw new \InvalidArgumentException('Invalid Position');
        }

        $slice = array_slice($this->items, $position, 1);
        return empty($slice) ? null : reset($slice);
    }

    /**
     * Get the last item in the collection.
     * @return mixed
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Perform $callback on each item in the collection. Stops if
     * $callback returns false.
     *
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * Get a flattened array of items in the collection.
     *
     * @param int $depth
     *
     * @return static
     */
    public function flatten($depth = INF)
    {
        return new static(Arr::flatten($this->items, $depth));
    }

    /**
     * Create a new collection containing only.
     *
     * @param mixed $keys
     * @return static
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Create a new collection containing all items except $keys.
     *
     * @param mixed $keys
     * @return static
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Filter the collection.
     *
     * @param callable|null $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::filter($this->items, $callback));
        }
        return new static(array_filter($this->items));
    }

    /**
     * Get a subset of unique items from Collection.
     *
     * @param int $flag
     * @return static
     */
    public function unique($flag = SORT_REGULAR)
    {
        return new static(Arr::unique($this->items, $flag));
    }

    /**
     * Search the collection and return the first corresponding item if successful.
     * If $needle is a callable then return the first item where the callable
     * returns true.
     *
     * @param mixed|callable $needle
     * @param bool           $strict
     * @return mixed|null
     */
    public function search($needle, $strict = false)
    {
        return Arr::search($this->items, $needle, $strict);
    }

    /**
     * Map $callback over each item in the collection.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function map(callable $callback)
    {
        $keys   = array_keys($this->items);
        $values = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $values));
    }

    /**
     * Merge $items with this collection.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->extractCollectibleItems($items)));
    }

    /**
     * Combine $items with this collection.
     *
     * @param mixed $values
     *
     * @return static
     */
    public function combine($values)
    {
        return new static(array_combine($this->items, $this->extractCollectibleItems($values)));
    }

    /**
     * Union the collection with $items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function union($items)
    {
        return new static($this->items + $this->extractCollectibleItems($items));
    }

    /**
     * Sort the collection.
     *
     * @param callable|null $callback
     *
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback
            ? uasort($items, $callback)
            : asort($items);

        return new static($items);
    }

    /**
     * Get a collection of items that have been flipped.
     *
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * @see array_keys()
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * @see array_values()
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * Get an item from the array if exists or $default.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }
        return $default;
    }

    /**
     * Check that all given keys exist.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach ($keys as $key) {
            if (!$this->offsetExists($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Implode the collection values.
     *
     * @see implode()
     * @param string        $glue
     * @param callable|null $callback
     * @return string
     */
    public function implode($glue = '', callable $callback = null)
    {
        return implode($glue, $callback ? array_map($callback, $this->items) : $this->items);
    }


    /**
     * @InheritDoc
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }
    /**
     * @InheritDoc
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } else if ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->items);
    }
    /**
     * @InheritDoc
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
    /**
     * @InheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }
    /**
     * @InheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }
    /**
     * @InheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
        return $this;
    }
    /**
     * @InheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
        return $this;
    }
    /**
     * @InheritDoc
     */
    public function count()
    {
        return count($this->items);
    }
    /**
     * @InheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}