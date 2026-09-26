<?php

namespace Footup\View;

use Footup\View\Parser;
use Footup\View\Compiler;
use Footup\View\FilterRegistry;
use Footup\View\AttributeRegistry;
use Footup\View\Cache;
use Footup\Exceptions\TemplateNotFound;
use Footup\Exceptions\SyntaxError;

/**
 * Lightweight Template Engine (Latte-style)
 * 
 * Supports:
 * - Variables: {$var}, {$obj->prop}
 * - Filters: {$text|upper|trim}
 * - Control: {if}...{/if}, {foreach}...{/foreach}
 * - Attributes: ui:if, ui:foreach, :if, :foreach
 * - No-escape: {! $html !}, {$html|raw}, {$html|noescape}
 * - Layouts: {extends}, {block}
 * - Includes: {include}
 */
class Engine implements TemplateEngineInterface
{
  /**
   * @var string[] Template search paths
   */
  private array $paths = [];
  private array $namespaces = []; // For Modules

  /**
   * @var Cache Template cache
   */
  private Cache $cache;

  /**
   * @var Parser Template parser
   */
  private Parser $parser;

  /**
   * @var Compiler Template compiler
   */
  private Compiler $compiler;

  /**
   * @var FilterRegistry Built-in filters
   */
  private FilterRegistry $filters;

  /**
   * @var AttributeRegistry Built-in attributes
   */
  private AttributeRegistry $attributes;

  /**
   * @var string[] Attribute prefixes to support (e.g., ['ui:', ':'])
   */
  private array $attributePrefixes = ['ui:', ':'];

  /**
   * @var string Compiled templates directory
   */
  private string $compiledDir;

  /**
   * @var bool Enable production mode (cache only)
   */
  private bool $productionMode = false;

  /**
   * @var array Shared template variables
   */
  private array $shared = [];

  /**
   * @var string[] File extensions to search (e.g., ['.ftup', '.kick'])
   * Searches in order: first match wins
   */
  private array $extensions = ['.ftup', '.kick'];

  public function __construct(array $paths = [], string $compiledDir = null, array $extensions = null)
  {
    $this->paths = $paths ?: [ui_path('/views')];
    $this->compiledDir = $compiledDir ?: storage_path('/compiled/views');

    // Set extensions (default: .ftup and .kick)
    if ($extensions !== null) {
      $this->extensions = array_map(
        fn($ext) => str_starts_with($ext, '.') ? $ext : '.' . $ext,
        $extensions
      );
    }

    // Ensure compiled directory exists
    if (!is_dir($this->compiledDir)) {
      mkdir($this->compiledDir, 0755, true);
    }

    // Initialize components
    $this->cache = new Cache($this->compiledDir);
    $this->parser = new Parser();
    $this->compiler = new Compiler();
    $this->filters = new FilterRegistry();
    $this->attributes = new AttributeRegistry();
  }

  /**
   * Render a template
   * 
   * @param string $template Template name (without extension)
   * @param array $data Template variables
   * @return string Rendered HTML
   * @throws TemplateNotFound
   * @throws SyntaxError
   */
  public function render(string $template, array $data = []): string
  {
    $templatePath = $this->find($template);

    if (!$templatePath) {
      throw new TemplateNotFound("Template '{$template}' not found in paths: " . implode(', ', array_map(fn($path) => strtr($path, [base_path('/') => '']), $this->paths)));
    }

    // Merge shared variables
    $data = array_merge($this->shared, $data);

    // Get compiled path
    $compiledPath = $this->getCompiledPath($templatePath);

    // Check if compiled version exists and is fresh
    if (!$this->productionMode || !file_exists($compiledPath) || filemtime($compiledPath) < filemtime($templatePath)) {
      // Parse and compile template
      $code = $this->parseAndCompile($templatePath);
      file_put_contents($compiledPath, $code);
    }

    // Execute compiled template
    return $this->executeTemplate($compiledPath, $data);
  }

  /**
   * Parse and compile a template
   * 
   * @param string $templatePath Full path to template file
   * @return string PHP code
   * @throws SyntaxError
   */
  private function parseAndCompile(string $templatePath): string
  {
    $source = file_get_contents($templatePath);

    // Parse template to AST
    $ast = $this->parser->parse($source, [
      'attributePrefixes' => $this->attributePrefixes,
      'filters' => $this->filters,
      'attributes' => $this->attributes,
    ]);

    // Compile AST to PHP
    $code = $this->compiler->compile($ast, [
      'templatePath' => $templatePath,
      'filters' => $this->filters,
      'attributes' => $this->attributes,
    ]);

    return $code;
  }

  /**
   * Execute compiled template
   * 
   * @param string $compiledPath Path to compiled template
   * @param array $data Variables
   * @return string Output
   */
  private function executeTemplate(string $compiledPath, array $data): string
  {
    // Extract variables for template scope
    extract($data, EXTR_SKIP);

    // Capture output
    ob_start();
    try {
      include $compiledPath;
      return ob_get_clean();
    } catch (\Throwable $e) {
      ob_end_clean();
      throw $e;
    }
  }

  /**
   * Register a module namespace for template lookup
   * 
   * @param string $namespace Module name (e.g., 'blog')
   * @param string $path Path to module views directory
   * @return self
   */
  public function registerNamespace(string $namespace, string $path): self
  {
    if (!isset($this->namespaces)) {
      $this->namespaces = [];
    }

    $this->namespaces[$namespace] = rtrim($path, '/');

    return $this;
  }

  /**
   * Get registered namespaces
   * 
   * @return array
   */
  public function getNamespaces(): array
  {
    return $this->namespaces ?? [];
  }


  /**
   * Find template file in paths
   * 
   * @param string $template Template name
   * @return string|null Full path or null if not found
   */
  private function find(string $template): ?string
  {
    // Check if template uses namespace syntax (namespace::template)
    if (strpos($template, '::') !== false) {
      [$namespace, $templateName] = explode('::', $template, 2);
      return $this->findInNamespace($namespace, $templateName);
    }

    // Normalize template name (replace . and / with proper paths)
    $templateName = str_replace('.', '/', $template);

    // Try each extension in order (.ftup, .kick, etc.)
    foreach ($this->extensions as $ext) {
      foreach ($this->paths as $path) {
        $fullPath = rtrim($path, '/') . '/' . $templateName . $ext;
        if (file_exists($fullPath)) {
          return $fullPath;
        }
      }
    }

    return null;
  }

  /**
   * Find template in a registered namespace
   * 
   * @param string $namespace Namespace name
   * @param string $template Template name (dot notation)
   * @return string|null Full path or null
   */
  private function findInNamespace(string $namespace, string $template): ?string
  {
    $namespaces = $this->getNamespaces();

    if (!isset($namespaces[$namespace])) {
      return null;
    }

    $basePath = $namespaces[$namespace];
    $templateName = str_replace('.', '/', $template);

    foreach ($this->extensions as $ext) {
      $fullPath = $basePath . '/' . $templateName . $ext;
      if (file_exists($fullPath)) {
        return $fullPath;
      }
    }

    return null;
  }

  /**
   * Get compiled template path
   * 
   * @param string $templatePath Original template path
   * @return string Compiled template path
   */
  private function getCompiledPath(string $templatePath): string
  {
    $hash = md5($templatePath);
    return $this->compiledDir . '/' . $hash . '.php';
  }

  /**
   * Share variables with all templates
   * 
   * @param string|array $key Variable name or array of variables
   * @param mixed $value Variable value
   * @return self
   */
  public function share($key, $value = null): self
  {
    if (is_array($key)) {
      $this->shared = array_merge($this->shared, $key);
    } else {
      $this->shared[$key] = $value;
    }

    return $this;
  }

  /**
   * Register a custom filter
   * 
   * @param string $name Filter name
   * @param callable $callback Filter function
   * @return self
   */
  public function filter(string $name, callable $callback): self
  {
    $this->filters->register($name, $callback);
    return $this;
  }

  /**
   * Register a custom attribute directive
   * 
   * @param string $name Attribute name (e.g., 'if', 'foreach')
   * @param callable $callback Handler function
   * @return self
   */
  public function attribute(string $name, callable $callback): self
  {
    $this->attributes->register($name, $callback);
    return $this;
  }

  /**
   * Add template search path
   * 
   * @param string $path Path to add
   * @return self
   */
  public function addPath(string $path): self
  {
    if (!in_array($path, $this->paths)) {
      $this->paths[] = $path;
    }

    return $this;
  }

  /**
   * Set attribute prefixes to support
   * 
   * @param array $prefixes E.g., ['ui:', ':']
   * @return self
   */
  public function setAttributePrefixes(array $prefixes): self
  {
    $this->attributePrefixes = $prefixes;
    return $this;
  }

  /**
   * Enable production mode (cache only, no recompilation)
   * 
   * @param bool $enabled
   * @return self
   */
  public function setProductionMode(bool $enabled): self
  {
    $this->productionMode = $enabled;
    return $this;
  }

  /**
   * Clear compiled template cache
   * 
   * @return self
   */
  public function clearCache(): self
  {
    $this->cache->clear();
    return $this;
  }

  /**
   * Get parser instance
   * 
   * @return Parser
   */
  public function getParser(): Parser
  {
    return $this->parser;
  }

  /**
   * Get compiler instance
   * 
   * @return Compiler
   */
  public function getCompiler(): Compiler
  {
    return $this->compiler;
  }

  /**
   * Get filter registry
   * 
   * @return FilterRegistry
   */
  public function getFilters(): FilterRegistry
  {
    return $this->filters;
  }

  /**
   * Get attribute registry
   * 
   * @return AttributeRegistry
   */
  public function getAttributes(): AttributeRegistry
  {
    return $this->attributes;
  }
}
