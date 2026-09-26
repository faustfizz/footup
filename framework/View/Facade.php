<?php

namespace Footup\View;

/**
 * Template Facade
 * 
 * Clean API for template operations
 * 
 * Usage:
 *   Template::render('posts/show', ['post' => $post])
 *   Template::share('user', auth()->user())
 *   Template::filter('plural', ...)
 *   Template::clearCache()
 */
class Template
{
    /**
     * Render a template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered output
     */
    public static function render(string $template, array $data = []): string
    {
        return TemplateServiceProvider::render($template, $data);
    }

    /**
     * Share data with all templates
     * 
     * @param string|array $key Key or array
     * @param mixed $value Value
     * @return void
     */
    public static function share($key, $value = null): void
    {
        TemplateServiceProvider::share($key, $value);
    }

    /**
     * Register a custom filter
     * 
     * @param string $name Filter name
     * @param callable $callback Filter function
     * @return void
     */
    public static function filter(string $name, callable $callback): void
    {
        TemplateServiceProvider::filter($name, $callback);
    }

    /**
     * Register a custom attribute directive
     * 
     * @param string $name Directive name
     * @param callable $callback Handler
     * @return void
     */
    public static function attribute(string $name, callable $callback): void
    {
        TemplateServiceProvider::attribute($name, $callback);
    }

    /**
     * Clear template cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        TemplateServiceProvider::clearCache();
    }

    /**
     * Get cache statistics
     * 
     * @return array Statistics
     */
    public static function cacheStats(): array
    {
        return TemplateServiceProvider::cacheStats();
    }

    /**
     * Get engine instance
     * 
     * @return Engine
     */
    public static function engine(): Engine
    {
        return TemplateServiceProvider::engine();
    }

    /**
     * Get layout manager instance
     * 
     * @return LayoutManager
     */
    public static function layouts(): LayoutManager
    {
        return TemplateServiceProvider::layoutManager();
    }

    /**
     * Add template search path
     * 
     * @param string $path Path to add
     * @return void
     */
    public static function addPath(string $path): void
    {
        TemplateServiceProvider::engine()->addPath($path);
    }

    /**
     * Set attribute prefixes
     * 
     * @param array $prefixes Prefixes (e.g., ['ui:', ':'])
     * @return void
     */
    public static function setAttributePrefixes(array $prefixes): void
    {
        TemplateServiceProvider::engine()->setAttributePrefixes($prefixes);
    }

    /**
     * Enable production mode
     * 
     * @return void
     */
    public static function enableProductionMode(): void
    {
        TemplateServiceProvider::engine()->setProductionMode(true);
    }

    /**
     * Disable production mode
     * 
     * @return void
     */
    public static function disableProductionMode(): void
    {
        TemplateServiceProvider::engine()->setProductionMode(false);
    }
}
