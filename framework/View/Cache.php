<?php

namespace Footup\View;

/**
 * Template Compilation Cache Manager
 * 
 * Manages caching of compiled templates
 */
class Cache
{
    /**
     * @var string Cache directory
     */
    private string $cacheDir;

    /**
     * @var bool Enable caching
     */
    private bool $enabled = true;

    /**
     * Constructor
     * 
     * @param string $cacheDir Directory for compiled templates
     */
    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = rtrim($cacheDir, '/') ?: storage_path('compiled/views');
        
        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cache file path for a template
     * 
     * @param string $templatePath Original template path
     * @return string Cache file path
     */
    public function getCachePath(string $templatePath): string
    {
        $hash = md5($templatePath);
        return $this->cacheDir . '/' . $hash . '.php';
    }

    /**
     * Check if cache exists and is fresh
     * 
     * @param string $templatePath Original template path
     * @return bool True if cache is fresh
     */
    public function isFresh(string $templatePath): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $cachePath = $this->getCachePath($templatePath);

        if (!file_exists($cachePath)) {
            return false;
        }

        if (!file_exists($templatePath)) {
            return false;
        }

        return filemtime($cachePath) >= filemtime($templatePath);
    }

    /**
     * Get cached content
     * 
     * @param string $templatePath Original template path
     * @return string|null Cached content or null
     */
    public function get(string $templatePath): ?string
    {
        if (!$this->isFresh($templatePath)) {
            return null;
        }

        $cachePath = $this->getCachePath($templatePath);
        return file_get_contents($cachePath);
    }

    /**
     * Put content in cache
     * 
     * @param string $templatePath Original template path
     * @param string $content Compiled PHP content
     */
    public function put(string $templatePath, string $content): void
    {
        $cachePath = $this->getCachePath($templatePath);
        file_put_contents($cachePath, $content);
    }

    /**
     * Clear specific cache entry
     * 
     * @param string $templatePath Original template path
     */
    public function forget(string $templatePath): void
    {
        $cachePath = $this->getCachePath($templatePath);
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): void
    {
        if (!is_dir($this->cacheDir)) {
            return;
        }

        $files = glob($this->cacheDir . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Enable caching
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable caching
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if caching is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get cache directory size
     * 
     * @return int Size in bytes
     */
    public function getSize(): int
    {
        $size = 0;
        
        if (!is_dir($this->cacheDir)) {
            return $size;
        }

        $files = glob($this->cacheDir . '/*.php');
        foreach ($files as $file) {
            $size += filesize($file);
        }

        return $size;
    }

    /**
     * Get number of cached templates
     * 
     * @return int
     */
    public function count(): int
    {
        if (!is_dir($this->cacheDir)) {
            return 0;
        }

        return count(glob($this->cacheDir . '/*.php'));
    }

    /**
     * Get cache size in human-readable format
     * 
     * @return string Formatted size (e.g., "2.5 MB")
     */
    public function getFormattedSize(): string
    {
        $size = $this->getSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        
        foreach ($units as $unit) {
            if ($size < 1024) {
                return round($size, 2) . ' ' . $unit;
            }
            $size /= 1024;
        }
        
        return round($size, 2) . ' TB';
    }

    /**
     * Prune old cache entries (older than $age seconds)
     * 
     * @param int $age Age in seconds (default: 24 hours)
     * @return int Number of files removed
     */
    public function prune(int $age = 86400): int
    {
        if (!is_dir($this->cacheDir)) {
            return 0;
        }

        $files = glob($this->cacheDir . '/*.php');
        $removed = 0;
        $now = time();

        foreach ($files as $file) {
            if ($now - filemtime($file) > $age) {
                unlink($file);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Get cache statistics
     * 
     * @return array Statistics
     */
    public function getStats(): array
    {
        return [
            'count' => $this->count(),
            'size_bytes' => $this->getSize(),
            'size_formatted' => $this->getFormattedSize(),
            'enabled' => $this->isEnabled(),
            'directory' => $this->cacheDir,
        ];
    }
}
