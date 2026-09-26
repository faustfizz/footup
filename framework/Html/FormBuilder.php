<?php

declare(strict_types=1);

namespace Footup\Html;

use Footup\Orm\DB;
use Footup\Orm\Model;

/*
|--------------------------------------------------------------------------
| FormBuilder — Generate HTML forms from Model definitions
|
| Usage:
|   Post::form()->render()
|   Post::formFor($post)->render()
|   $form = new FormBuilder(Post::class);
|   $form->field('title')->label('Post Title')->placeholder('Enter...')
|   echo $form->render();
|--------------------------------------------------------------------------
*/

class FormBuilder
{
    protected string $modelClass;
    protected ?Model $model = null;
    protected array $fields = [];
    protected array $exclude = ['id', 'created_at', 'updated_at'];
    protected array $fieldConfigs = [];
    protected string $method = 'POST';
    protected ?string $action = null;
    protected array $attributes = ['class' => 'form'];
    protected array $fileFields = [];
    protected array $urlFields = [];
    protected array $enumFields = [];  // ['status' => ['draft', 'published', 'archived']]

    public function __construct(string $modelClass, ?Model $model = null)
    {
        $this->modelClass = $modelClass;
        $this->model = $model;
        $this->loadFileFieldsConfig();
        $this->loadUrlFieldsConfig();
        $this->loadEnumFieldsConfig();
    }

    /**
     * Load file field configuration
     * Config format: ['image' => ['jpg', 'png'], 'avatar' => ['jpg', 'png'], 'video' => ['mp4']]
     */
    protected function loadFileFieldsConfig(): void
    {
        // Check if model has fileFields property
        try {
            $instance = new $this->modelClass();
            if (property_exists($instance, 'fileFields')) {
                $this->fileFields = $instance->fileFields;
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Load URL field configuration
     * Config format: ['website' => true, 'social_url' => true]
     * Or auto-detect from field names
     */
    protected function loadUrlFieldsConfig(): void
    {
        // Check if model has urlFields property
        try {
            $instance = new $this->modelClass();
            if (property_exists($instance, 'urlFields')) {
                $this->urlFields = $instance->urlFields;
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Load enum field configuration
     * Config format: ['status' => ['draft', 'published', 'archived'], 'role' => ['admin', 'user']]
     * Can also auto-detect from database column type
     */
    protected function loadEnumFieldsConfig(): void
    {
        // Check if model has enumFields property
        try {
            $instance = new $this->modelClass();
            if (property_exists($instance, 'enumFields')) {
                $this->enumFields = $instance->enumFields;
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Set file fields configuration
     */
    public function fileFields(array $fields): self
    {
        $this->fileFields = $fields;
        return $this;
    }

    /**
     * Set URL fields configuration
     */
    public function urlFields(array $fields): self
    {
        $this->urlFields = $fields;
        return $this;
    }

    /**
     * Set enum fields configuration
     * Config format: ['status' => ['draft', 'published', 'archived']]
     */
    public function enumFields(array $fields): self
    {
        $this->enumFields = $fields;
        return $this;
    }

    /**
     * Exclude fields from auto-generation
     */
    public function exclude(array $fields): self
    {
        $this->exclude = array_merge($this->exclude, $fields);
        return $this;
    }

    /**
     * Set form method (GET, POST, etc)
     */
    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set form action URL
     */
    public function action(string $url): self
    {
        $this->action = $url;
        return $this;
    }

    /**
     * Set HTML attributes on form
     */
    public function attributes(array $attrs): self
    {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }

    /**
     * Configure a specific field
     */
    public function field(string $name): FieldConfig
    {
        if (!isset($this->fieldConfigs[$name])) {
            $this->fieldConfigs[$name] = new FieldConfig($name);
        }
        return $this->fieldConfigs[$name];
    }

    /**
     * Get all columns from model table
     */
    protected function getModelColumns(): array
    {
        try {
            $table = $this->modelClass::tableName();
            
            // Get columns from database
            $driver = DB::connection()->driver();

            if ($driver === 'sqlite') {
                $result = DB::connection()->pdo()->query(
                    "PRAGMA_TABLE_INFO('{$table}')"
                );
                // Parse SQLite columns
            } elseif ($driver === 'mysql') {
                $result = DB::connection()->pdo()->query(
                    "SELECT COLUMN_NAME as name, COLUMN_TYPE as type, IS_NULLABLE as is_nullable FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = '{$table}' AND TABLE_SCHEMA = DATABASE()"
                );
            } elseif ($driver === 'pgsql') {
                $result = DB::connection()->pdo()->query(
                    "SELECT column_name as name, data_type as type, is_nullable FROM information_schema.columns 
                    WHERE table_name = '{$table}'"
                );
            } else {
                throw new \Exception("Unsupported database driver: {$driver}");
            }
            
            $columns = [];
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $name = $row['name'];
                if (!in_array($name, $this->exclude)) {
                    $type = $row['type'];
                    $enumValues = [];

                    if ($driver !== 'sqlite') {
                        $row['notnull'] = $row['is_nullable'] === 'NO'; 
                    }
                    
                    // Extract enum values if column type is ENUM('val1', 'val2', ...)
                    if (preg_match("/^ENUM\((.*)\)$/i", $type, $matches)) {
                        // Parse ENUM('value1', 'value2', 'value3')
                        $valueString = $matches[1];
                        $enumValues = array_map(function($v) {
                            return trim($v, " '\"");
                        }, str_getcsv($valueString, ','));
                    }
                    
                    $columns[$name] = [
                        'type' => $type,
                        'notnull' => (bool) $row['notnull'],
                        'enum_values' => $enumValues,  // Will be [] if not an enum
                    ];
                }
            }
            return $columns;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Guess field type from column name and type
     */
    protected function guessFieldType(string $columnName, string $columnType): string
    {
        // Check for file fields first (by name)
        if (array_key_exists($columnName, $this->fileFields)) {
            return 'file';
        }

        // Check for URL fields (by name)
        if (array_key_exists($columnName, $this->urlFields) || $this->isUrlField($columnName)) {
            return 'url';
        }

        // Check for ENUM columns
        if (preg_match("/^ENUM\(/i", $columnType)) {
            return 'select';
        }

        // Then check by column type
        $type = strtolower($columnType);
        
        if (strpos($type, 'int') !== false) {
            return 'number';
        }
        if (strpos($type, 'bool') !== false) {
            return 'checkbox';
        }
        if (strpos($type, 'text') !== false) {
            return 'textarea';
        }
        if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
            return 'datetime-local';
        }
        if (strpos($type, 'date') !== false) {
            return 'date';
        }
        
        return 'text';
    }

    /**
     * Check if field name indicates a URL field
     * Patterns: url, url_*, *_url
     */
    protected function isUrlField(string $fieldName): bool
    {
        $name = strtolower($fieldName);
        
        // Exact match
        if ($name === 'url') {
            return true;
        }
        
        // Prefix: url_*
        if (strpos($name, 'url_') === 0) {
            return true;
        }
        
        // Suffix: *_url
        if (substr($name, -4) === '_url') {
            return true;
        }
        
        return false;
    }

    /**
     * Render the form HTML
     */
    public function render(): string
    {
        $columns = $this->getModelColumns();
        
        $html = '<form method="' . htmlspecialchars($this->method) . '"';
        
        if ($this->action) {
            $html .= ' action="' . htmlspecialchars($this->action) . '"';
        }
        
        foreach ($this->attributes as $key => $value) {
            $html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        $html .= ">\n";
        
        // CSRF token for POST
        if ($this->method === 'POST') {
            $html .= '  <input type="hidden" name="_token" value="' . csrf_token() . '">' . "\n";
        }
        
        // Render fields
        foreach ($columns as $name => $info) {
            $config = $this->fieldConfigs[$name] ?? new FieldConfig($name);
            
            // Auto-set type if not already set
            if ($config->type === 'text') {
                $config->type = $this->guessFieldType($name, $info['type']);
                
                // If it's a file field, add accepted extensions
                if ($config->type === 'file' && array_key_exists($name, $this->fileFields)) {
                    $extensions = $this->fileFields[$name];
                    $config->attributes([
                        'accept' => '.' . implode(',.', $extensions)
                    ]);
                }
                
                // If it's an enum field, set select options
                if ($config->type === 'select') {
                    // First check if there are manual enum values configured
                    if (array_key_exists($name, $this->enumFields)) {
                        $enumValues = $this->enumFields[$name];
                    } else if (!empty($info['enum_values'])) {
                        // Use values extracted from ENUM column type
                        $enumValues = $info['enum_values'];
                    } else {
                        $enumValues = [];
                    }
                    
                    // Convert values to options array (value => label)
                    $options = [];
                    foreach ($enumValues as $value) {
                        $label = ucfirst(str_replace('_', ' ', $value));
                        $options[$value] = $label;
                    }
                    $config->options($options);
                }
            }
            
            // Get value from model if available
            if ($this->model && isset($this->model->attributes[$name])) {
                $config->value = $this->model->attributes[$name];
            }
            
            $html .= $config->render() . "\n";
        }
        
        $html .= '  <button type="submit" class="btn btn-primary">Submit</button>' . "\n";
        $html .= '</form>';
        
        return $html;
    }

    /**
     * Render to string automatically
     */
    public function __toString(): string
    {
        return $this->render();
    }
}