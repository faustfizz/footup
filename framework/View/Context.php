<?php

namespace Footup\View;

/**
 * Template Context - Manages template rendering context
 */
class Context
{
    /**
     * @var array Template variables
     */
    private array $variables = [];

    /**
     * @var array Shared variables across templates
     */
    private array $shared = [];

    /**
     * @var array Current blocks being rendered
     */
    private array $blocks = [];

    /**
     * @var array Loop stack
     */
    private array $loops = [];

    /**
     * Constructor
     * 
     * @param array $variables Initial variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * Set a variable
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Get a variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->variables[$key] ?? $default;
    }

    /**
     * Check if variable exists
     * 
     * @param string $key Variable name
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->variables[$key]);
    }

    /**
     * Get all variables
     * 
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->shared, $this->variables);
    }

    /**
     * Share a variable with all templates
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return self
     */
    public function share(string $key, $value): self
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * Push loop context
     * 
     * @param array $data Loop data
     */
    public function pushLoop(array $data): void
    {
        $this->loops[] = (object)$data;
    }

    /**
     * Pop loop context
     */
    public function popLoop(): void
    {
        array_pop($this->loops);
    }

    /**
     * Get current loop
     * 
     * @return object|null Current loop or null
     */
    public function getLoop(): ?object
    {
        return end($this->loops) ?: null;
    }

    /**
     * Set a block
     * 
     * @param string $name Block name
     * @param callable $content Block content callable
     */
    public function setBlock(string $name, callable $content): void
    {
        $this->blocks[$name] = $content;
    }

    /**
     * Get a block
     * 
     * @param string $name Block name
     * @return callable|null
     */
    public function getBlock(string $name): ?callable
    {
        return $this->blocks[$name] ?? null;
    }

    /**
     * Render a block
     * 
     * @param string $name Block name
     * @return string Rendered block
     */
    public function renderBlock(string $name): string
    {
        $block = $this->getBlock($name);
        if (!$block) {
            return '';
        }

        ob_start();
        $block();
        return ob_get_clean();
    }

    /**
     * Check if block exists
     * 
     * @param string $name Block name
     * @return bool
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->blocks[$name]);
    }
}
