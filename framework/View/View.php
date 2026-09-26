<?php

namespace Footup\View;

/**
 * View Class - Render templates
 * 
 * Used in controllers:
 *   return $this->view('posts.show', ['post' => $post]);
 *   return view('users.profile', ['user' => auth()->user()]);
 */
class View
{
  /**
   * Render a template
   * 
   * @param string $template Template name (dot notation)
   * @param array $data Template data
   * @return string Rendered HTML
   */
  public static function __callStatic(string $method, array $args = [])
  {
    if ($method !== 'render') {
      throw new \BadMethodCallException("Method $method does not exist on View.");
    }

    if (count($args) < 1) {
      throw new \InvalidArgumentException("View::render() requires at least one argument: the template name.");
    }

    [$template, $data] = $args + [null, []];

    return static::make($template, $data)->render();
  }
  /**
   * Make a view instance (static factory)
   * 
   * @param string $template Template name
   * @param array $data Template data
   * @return static
   */
  public static function make(string $template, array $data = []): static
  {
    $view = new static();
    $view->template = $template;
    $view->data = $data;
    return $view;
  }

  /**
   * @var string Template name
   */
  protected string $template = '';

  /**
   * @var array Template data
   */
  protected array $data = [];

  /**
   * Add data to view
   * 
   * @param string|array $key Key or array
   * @param mixed $value Value
   * @return self
   */
  public function with($key, $value = null): self
  {
    if (is_array($key)) {
      $this->data = array_merge($this->data, $key);
    } else {
      $this->data[$key] = $value;
    }

    return $this;
  }

  /**
   * Render the view (instance method)
   * Can render current template or render a different one
   * 
   * @param string|null $template Template name (optional, uses current if null)
   * @param array $data Additional data (optional)
   * @return string Rendered HTML
   */
  public function render(?string $template = null, array $data = []): string
  {
    $tpl = $template ?? $this->template;
    $vars = array_merge($this->data, $data);

    return TemplateServiceProvider::render($tpl, $vars);
  }

  /**
   * Convert to string (auto-render)
   * 
   * @return string
   */
  public function __toString(): string
  {
    return $this->render();
  }
}
