<?php

declare(strict_types=1);

namespace Footup\Html;

use Footup\Orm\DB;
use Footup\Orm\Model;

/**
 * Field configuration helper
 */
class FieldConfig
{
    public string $name;
    public string $type = 'text';
    public ?string $label = null;
    public ?string $placeholder = null;
    public ?string $value = null;
    public array $options = [];
    public bool $required = false;
    public array $attributes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    /**
     * Set field label
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set placeholder text
     */
    public function placeholder(string $text): self
    {
        $this->placeholder = $text;
        return $this;
    }

    /**
     * Set field type
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set field value
     */
    public function value(mixed $val): self
    {
        $this->value = (string) $val;
        return $this;
    }

    /**
     * Mark as required
     */
    public function required(bool $req = true): self
    {
        $this->required = $req;
        return $this;
    }

    /**
     * Set select/radio options
     */
    public function options(array $opts): self
    {
        $this->options = $opts;
        return $this;
    }

    /**
     * Set custom HTML attributes
     */
    public function attributes(array $attrs): self
    {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }

    /**
     * Populate from relationship (for select dropdowns)
     */
    public function fromRelation(string $relationMethod, string $displayKey = 'name'): self
    {
        // This would be implemented to call a model relationship
        // and populate options from it
        return $this;
    }

    /**
     * Render field HTML
     */
    public function render(): string
    {
        $html = '<div class="form-group">' . "\n";
        
        if ($this->label && $this->type !== 'checkbox') {
            $html .= '  <label for="' . htmlspecialchars($this->name) . '">' 
                . htmlspecialchars($this->label);
            if ($this->required) {
                $html .= ' <span class="required">*</span>';
            }
            $html .= '</label>' . "\n";
        }
        
        match ($this->type) {
            'textarea' => $html .= $this->renderTextarea(),
            'select' => $html .= $this->renderSelect(),
            'checkbox' => $html .= $this->renderCheckbox(),
            'radio' => $html .= $this->renderRadio(),
            'file' => $html .= $this->renderFile(),
            'url' => $html .= $this->renderUrl(),
            default => $html .= $this->renderInput(),
        };
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render text input
     */
    protected function renderInput(): string
    {
        $html = '  <input type="' . htmlspecialchars($this->type) . '" ';
        $html .= 'name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        
        if ($this->value !== null) {
            $html .= 'value="' . htmlspecialchars($this->value) . '" ';
        }
        
        if ($this->placeholder) {
            $html .= 'placeholder="' . htmlspecialchars($this->placeholder) . '" ';
        }
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= '/>' . "\n";
        
        return $html;
    }

    /**
     * Render textarea
     */
    protected function renderTextarea(): string
    {
        $html = '  <textarea name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        
        if ($this->placeholder) {
            $html .= 'placeholder="' . htmlspecialchars($this->placeholder) . '" ';
        }
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= '>';
        
        if ($this->value) {
            $html .= htmlspecialchars($this->value);
        }
        
        $html .= '</textarea>' . "\n";
        
        return $html;
    }

    /**
     * Render select dropdown
     */
    protected function renderSelect(): string
    {
        $html = '  <select name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= ">\n";
        
        if (!$this->required) {
            $html .= '    <option value="">-- Select --</option>' . "\n";
        }
        
        foreach ($this->options as $val => $label) {
            $selected = ($this->value == $val) ? ' selected' : '';
            $html .= '    <option value="' . htmlspecialchars($val) . '"' . $selected . '>' 
                . htmlspecialchars($label) . '</option>' . "\n";
        }
        
        $html .= '  </select>' . "\n";
        
        return $html;
    }

    /**
     * Render checkbox
     */
    protected function renderCheckbox(): string
    {
        $html = '  <input type="checkbox" name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        $html .= 'value="1" ';
        
        if ($this->value && $this->value !== '0' && $this->value !== 'false') {
            $html .= 'checked ';
        }
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= '/>' . "\n";
        
        if ($this->label) {
            $html .= '  <label for="' . htmlspecialchars($this->name) . '" class="checkbox-label">' 
                . htmlspecialchars($this->label) . '</label>' . "\n";
        }
        
        return $html;
    }

    /**
     * Render radio buttons
     */
    protected function renderRadio(): string
    {
        $html = '';
        
        foreach ($this->options as $val => $label) {
            $checked = ($this->value == $val) ? ' checked' : '';
            $id = htmlspecialchars($this->name . '_' . $val);
            
            $html .= '  <div class="radio">' . "\n";
            $html .= '    <input type="radio" name="' . htmlspecialchars($this->name) . '" ';
            $html .= 'id="' . $id . '" ';
            $html .= 'value="' . htmlspecialchars($val) . '"' . $checked . ' />' . "\n";
            $html .= '    <label for="' . $id . '">' . htmlspecialchars($label) . '</label>' . "\n";
            $html .= '  </div>' . "\n";
        }
        
        return $html;
    }

    /**
     * Render file upload field
     */
    protected function renderFile(): string
    {
        $html = '  <input type="file" ';
        $html .= 'name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= '/>' . "\n";
        
        return $html;
    }

    /**
     * Render URL field
     */
    protected function renderUrl(): string
    {
        $html = '  <input type="url" ';
        $html .= 'name="' . htmlspecialchars($this->name) . '" ';
        $html .= 'id="' . htmlspecialchars($this->name) . '" ';
        
        if ($this->value !== null) {
            $html .= 'value="' . htmlspecialchars($this->value) . '" ';
        }
        
        if ($this->placeholder) {
            $html .= 'placeholder="' . htmlspecialchars($this->placeholder) . '" ';
        }
        
        if ($this->required) {
            $html .= 'required ';
        }
        
        foreach ($this->attributes as $key => $val) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($val) . '" ';
        }
        
        $html .= '/>' . "\n";
        
        return $html;
    }
}
