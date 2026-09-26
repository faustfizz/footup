<?php

namespace Footup\View;

/**
 * Template Engine Interface
 * 
 * Minimal contract that all template engines must implement.
 * Only requires the render() method - everything else is optional.
 */
interface TemplateEngineInterface
{
    /**
     * Render a template
     * 
     * @param string $template Template name
     * @param array $data Template variables
     * @return string Rendered output
     * 
     * @throws \Exception If template not found or rendering fails
     */
    public function render(string $template, array $data = []): string;
}
