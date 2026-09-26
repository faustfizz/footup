<?php

namespace Footup\View;

/**
 * Layout Manager - Manage template inheritance
 * 
 * Handles:
 * - {extends 'layout.html'}
 * - {block name}...{/block}
 * - Block inheritance and override
 * - Nested layouts
 */
class LayoutManager
{
    /**
     * @var Engine Template engine instance
     */
    private Engine $engine;

    /**
     * @var array Block definitions
     */
    private array $blocks = [];

    /**
     * @var string Parent layout path
     */
    private ?string $parentLayout = null;

    /**
     * @var array Layout stack for nested layouts
     */
    private array $layoutStack = [];

    /**
     * Constructor
     * 
     * @param Engine $engine Template engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Register a block
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
     * Check if block exists
     * 
     * @param string $name Block name
     * @return bool
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->blocks[$name]);
    }

    /**
     * Render a block
     * 
     * @param string $name Block name
     * @return string Rendered block
     */
    public function renderBlock(string $name): string
    {
        if (!$this->hasBlock($name)) {
            return '';
        }

        $block = $this->getBlock($name);
        
        ob_start();
        $block();
        return ob_get_clean();
    }

    /**
     * Set parent layout
     * 
     * @param string $path Parent layout path
     */
    public function setParentLayout(string $path): void
    {
        $this->parentLayout = $path;
    }

    /**
     * Get parent layout
     * 
     * @return string|null
     */
    public function getParentLayout(): ?string
    {
        return $this->parentLayout;
    }

    /**
     * Check if has parent layout
     * 
     * @return bool
     */
    public function hasParentLayout(): bool
    {
        return $this->parentLayout !== null;
    }

    /**
     * Push layout to stack (for nested layouts)
     * 
     * @param string $path Layout path
     */
    public function pushLayout(string $path): void
    {
        $this->layoutStack[] = $path;
    }

    /**
     * Pop layout from stack
     * 
     * @return string|null
     */
    public function popLayout(): ?string
    {
        return array_pop($this->layoutStack);
    }

    /**
     * Get current layout depth
     * 
     * @return int
     */
    public function getLayoutDepth(): int
    {
        return count($this->layoutStack);
    }

    /**
     * Render with layout inheritance
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered output
     */
    public function render(string $template, array $data = []): string
    {
        // Render main template
        $output = $this->engine->render($template, $data);

        // If has parent layout, render with inheritance
        if ($this->hasParentLayout()) {
            $parentPath = $this->getParentLayout();
            // Replace blocks in parent with blocks from child
            $output = $this->renderWithParent($parentPath, $data);
        }

        return $output;
    }

    /**
     * Render child template within parent layout
     * 
     * @param string $parentPath Parent layout path
     * @param array $data Template data
     * @return string Rendered output
     */
    private function renderWithParent(string $parentPath, array $data): string
    {
        // Push current layout to stack
        $this->pushLayout($parentPath);

        // Render parent template
        $output = $this->engine->render($parentPath, $data);

        // Pop layout from stack
        $this->popLayout();

        return $output;
    }

    /**
     * Register default blocks (fallback content)
     * 
     * @param array $blocks Blocks with names and default content
     */
    public function registerDefaultBlocks(array $blocks): void
    {
        foreach ($blocks as $name => $content) {
            if (!$this->hasBlock($name)) {
                $this->setBlock($name, fn() => $content);
            }
        }
    }

    /**
     * Get all blocks
     * 
     * @return array Block names
     */
    public function getAllBlocks(): array
    {
        return array_keys($this->blocks);
    }

    /**
     * Clear all blocks
     */
    public function clearBlocks(): void
    {
        $this->blocks = [];
        $this->parentLayout = null;
        $this->layoutStack = [];
    }
}
