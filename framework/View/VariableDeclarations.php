<?php

namespace Footup\View;

/**
 * Parser Extension - Variable Declaration Support
 * 
 * Add this to Parser.php to support:
 * - {var $name = value}
 * - {set $name = value}
 * - {capture $name}...{/capture}
 */
class VariableDeclarationSupport
{
    /**
     * Parse variable declaration: {var $name = expression}
     * 
     * @param Parser $parser The parser instance
     * @param string $content Declaration content (e.g., "$name = 'value'")
     * @return array AST node
     */
    public static function parseVarDeclaration(string $content): array
    {
        // Parse: {var $name = expression}
        if (!preg_match('/^\s*(\$[\w]+)\s*=\s*(.+)$/s', trim($content), $matches)) {
            throw new \Footup\Template\Exceptions\SyntaxError(
                "Invalid var declaration: {var $content}"
            );
        }

        $varName = $matches[1];
        $expression = trim($matches[2]);

        return [
            'type' => 'var_declaration',
            'name' => $varName,
            'expression' => $expression,
            'escape' => false  // Variables aren't echoed, just stored
        ];
    }

    /**
     * Parse variable assignment: {set $name = expression}
     * 
     * @param string $content Assignment content
     * @return array AST node
     */
    public static function parseVarAssignment(string $content): array
    {
        // Parse: {set $name = expression}
        if (!preg_match('/^\s*(\$[\w]+)\s*=\s*(.+)$/s', trim($content), $matches)) {
            throw new \Footup\Template\Exceptions\SyntaxError(
                "Invalid set statement: {set $content}"
            );
        }

        $varName = $matches[1];
        $expression = trim($matches[2]);

        return [
            'type' => 'var_assignment',
            'name' => $varName,
            'expression' => $expression
        ];
    }

    /**
     * Parse capture block: {capture $name}...{/capture}
     * 
     * @param string $varName Variable name to capture to
     * @param array $body Body AST nodes
     * @return array AST node
     */
    public static function parseCaptureBlock(string $varName, array $body): array
    {
        return [
            'type' => 'capture',
            'name' => $varName,
            'body' => $body
        ];
    }
}

/**
 * Compiler Extension - Generate PHP for variable declarations
 * 
 * Add this to Compiler.php compile() method
 */
class VariableDeclarationCompiler
{
    /**
     * Compile var declaration to PHP
     * 
     * @param array $node AST node
     * @return string PHP code
     */
    public static function compileVarDeclaration(array $node): string
    {
        $name = $node['name'];
        $expression = $node['expression'];

        // Handle different expression types
        if (str_contains($expression, '|')) {
            // Filter chain: $var|filter1|filter2
            return self::compileFilteredExpression($name, $expression);
        }

        // Simple assignment
        return "<?php {$name} = {$expression}; ?>";
    }

    /**
     * Compile var assignment to PHP
     * 
     * @param array $node AST node
     * @return string PHP code
     */
    public static function compileVarAssignment(array $node): string
    {
        $name = $node['name'];
        $expression = $node['expression'];

        return "<?php {$name} = {$expression}; ?>";
    }

    /**
     * Compile capture block to PHP
     * 
     * @param array $node AST node
     * @param Compiler $compiler Compiler instance
     * @return string PHP code
     */
    public static function compileCaptureBlock(array $node, Compiler $compiler): string
    {
        $name = $node['name'];
        $body = $node['body'] ?? [];

        $php = "<?php ob_start(); ?>";

        // Compile body
        foreach ($body as $bodyNode) {
            $php .= $compiler->compileNode($bodyNode);
        }

        $php .= "<?php {$name} = ob_get_clean(); ?>";

        return $php;
    }

    /**
     * Compile filtered expression
     * 
     * @param string $name Variable name
     * @param string $expression Expression with filters
     * @return string PHP code
     */
    private static function compileFilteredExpression(string $name, string $expression): string
    {
        // Parse: $var|filter1|filter2:arg
        $parts = explode('|', $expression);
        $value = trim(array_shift($parts));

        $php = "<?php {$name} = {$value}; ";

        foreach ($parts as $filter) {
            $filter = trim($filter);

            if (preg_match('/^(\w+)(?::(.+))?$/', $filter, $m)) {
                $filterName = $m[1];
                $args = $m[2] ?? '';

                if ($args) {
                    // Convert filter args to PHP
                    $args = self::parseFilterArgs($args);
                    $php .= "{$name} = FilterExecutor::apply('{$filterName}', {$name}, [{$args}]); ";
                } else {
                    $php .= "{$name} = FilterExecutor::apply('{$filterName}', {$name}); ";
                }
            }
        }

        $php .= "?>";
        return $php;
    }

    /**
     * Parse filter arguments
     * 
     * @param string $args Arguments string
     * @return string PHP array format
     */
    private static function parseFilterArgs(string $args): string
    {
        // Convert: 'arg1', 'arg2' to: 'arg1', 'arg2'
        // Or: 50 to: 50
        $parts = preg_split('/\s*,\s*/', $args);
        $phpArgs = [];

        foreach ($parts as $arg) {
            $arg = trim($arg);
            // If looks like string, quote it
            if (!is_numeric($arg) && !str_starts_with($arg, '$') && !str_starts_with($arg, "'") && !str_starts_with($arg, '"')) {
                $phpArgs[] = "'{$arg}'";
            } else {
                $phpArgs[] = $arg;
            }
        }

        return implode(', ', $phpArgs);
    }
}

// ============================================================================
// INTEGRATION INSTRUCTIONS
// ============================================================================

/*
To add variable declaration support to the Footup template engine:

1. IN PARSER.PHP - Add to parse() method:

    protected function parseTag(string $tag): ?array
    {
        // ... existing code ...
        
        // Add variable declaration parsing
        if (str_starts_with($tag, 'var ')) {
            return VariableDeclarationSupport::parseVarDeclaration(
                substr($tag, 4)
            );
        }
        
        if (str_starts_with($tag, 'set ')) {
            return VariableDeclarationSupport::parseVarAssignment(
                substr($tag, 4)
            );
        }
        
        if (str_starts_with($tag, 'capture ')) {
            return VariableDeclarationSupport::parseCaptureBlock(
                substr($tag, 8),
                $this->parseUntil('/capture')
            );
        }
        
        // ... rest of parsing ...
    }

2. IN COMPILER.PHP - Add to compile() method:

    protected function compileNode(array $node): string
    {
        $type = $node['type'] ?? null;
        
        return match($type) {
            // ... existing cases ...
            'var_declaration' => VariableDeclarationCompiler::compileVarDeclaration($node),
            'var_assignment' => VariableDeclarationCompiler::compileVarAssignment($node),
            'capture' => VariableDeclarationCompiler::compileCaptureBlock($node, $this),
            default => '',
        };
    }

3. TEST IT:

    $template = <<<'EOT'
    {var $name = 'Alice'}
    {var $age = 30}
    {var $message = $name . ' is ' . $age}
    
    <p>{$message}</p>
    EOT;
    
    $engine = new Engine(['resources/views']);
    $output = $engine->render('test', []);
    // Should output: <p>Alice is 30</p>

EXAMPLES:

{var $x = 5}
{var $greeting = 'Hello'}
{var $items = [1, 2, 3]}
{var $upper = $text|upper}
{var $count = $posts|count}
{var $name = $user->name ?? 'Guest'}

{set $total = $total + $price}

{capture $html}
    <div class="card">
        <h2>{$title}</h2>
    </div>
{/capture}

FEATURES SUPPORTED:

✅ Simple literals: {var $x = 'string'}, {var $x = 123}, {var $x = true}
✅ Arrays: {var $arr = [1, 2, 3]}, {var $map = ['a' => 1]}
✅ Variables: {var $x = $user}, {var $y = $post->title}
✅ Expressions: {var $x = $a + $b}, {var $x = $a ? 'yes' : 'no'}
✅ Filters: {var $x = $text|upper|trim}
✅ Null coalescing: {var $x = $y ?? 'default'}
✅ Property access: {var $x = $obj->prop}, {var $x = $arr['key']}

NOT SUPPORTED (by design):

❌ Function calls: {var $x = doSomething()}
❌ Database queries: {var $x = User::all()}
❌ Side effects: {var $x = $this->save()}

This keeps templates clean and simple!
*/
