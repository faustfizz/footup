<?php

namespace Footup\View;

use Footup\Exceptions\CompilationError;

/**
 * Template Compiler - Convert AST to executable PHP code
 */
class Compiler
{
    /**
     * @var int Indentation level
     */
    private int $indent = 0;

    /**
     * @var string Compiled output
     */
    private string $output = '';

    /**
     * @var array Compiler options
     */
    private array $options = [];
    private array $nodes = [];

    /**
     * Compile AST to PHP code
     * 
     * @param array $nodes AST nodes
     * @param array $options Compiler options
     * @return string PHP code
     * @throws CompilationError
     */
    public function compile(array $nodes, array $options = []): string
    {
        $this->nodes = $nodes;
        $this->options = $options;
        $this->output = '';
        $this->indent = 0;

        // Start PHP code
        $this->emit("<?php\n");
        $this->emit("// Compiled Footup Template\n");
        $this->emit("// DO NOT EDIT\n\n");

        // Initialize loop stack
        $this->emit("\$__loops = [];\n");
        $this->emit("\$__loop = null;\n\n");

        // Compile nodes
        foreach ($nodes as $node) {
            $this->compileNode($node);
        }

        // Add closing PHP tag if needed
        if (substr($this->output, -2) !== '?>') {
            $this->emit("\n?>");
        }

        return $this->output;
    }

    /**
     * Compile a single node
     * 
     * @param array $node AST node
     * @throws CompilationError
     */
    private function compileNode(array $node): void
    {
        $type = $node['type'] ?? null;

        match ($type) {
            'text' => $this->compileText($node),
            'output' => $this->compileOutput($node),
            'output_raw' => $this->compileOutputRaw($node),
            'if' => $this->compileIf($node),
            'foreach' => $this->compileForeach($node),
            'while' => $this->compileWhile($node),
            'for' => $this->compileFor($node),
            'block' => $this->compileBlock($node),
            'block_end' => null, // Handled by block start
            'extends' => $this->compileExtends($node),
            'include' => $this->compileInclude($node),
            'expression' => $this->compileExpression($node),
            default => throw new CompilationError("Unknown node type: {$type}"),
        };
    }

    /**
     * Compile text node
     */
    private function compileText(array $node): void
    {
        $content = $node['content'];
        // Escape single quotes for PHP string
        $content = str_replace("'", "\\'", $content);
        $this->emit("echo '{$content}';\n");
    }

    /**
     * Compile output node (with auto-escaping)
     */
    private function compileOutput(array $node): void
    {
        $var = $node['var'];
        $filters = $node['filters'] ?? [];

        // Start with variable
        $expr = "({$var})";

        // Apply filters
        foreach ($filters as $filter) {
            $name = $filter['name'];
            $args = $filter['args'] ?? [];

            // Check if it's an escape-related filter
            if ($name === 'raw' || $name === 'noescape') {
                // Mark as raw
                $expr = "({$expr})"; // Just wrap, htmlspecialchars won't be applied
                continue;
            }

            // Apply filter function
            if (!empty($args)) {
                $argsStr = implode(', ', array_map(fn($a) => is_numeric($a) ? $a : "'{$a}'", $args));
                $expr = "\\Footup\\View\\FilterRegistry::apply('{$name}', {$expr}, [{$argsStr}])";
            } else {
                $expr = "\\Footup\\View\\FilterRegistry::apply('{$name}', {$expr})";
            }
        }

        // Apply auto-escaping if not marked as raw
        $isRaw = false;
        foreach ($filters as $filter) {
            if ($filter['name'] === 'raw' || $filter['name'] === 'noescape') {
                $isRaw = true;
                break;
            }
        }

        if (!$isRaw) {
            $expr = "htmlspecialchars({$expr}, ENT_QUOTES, 'UTF-8')";
        }

        $this->emit("echo {$expr};\n");
    }

    /**
     * Compile raw output (no escaping)
     */
    private function compileOutputRaw(array $node): void
    {
        $var = $node['var'];
        $this->emit("echo ({$var});\n");
    }

    /**
     * Compile if statement
     */
    private function compileIf(array $node): void
    {
        $condition = $node['condition'];
        $body = $node['body'] ?? [];
        $elseifs = $node['elseifs'] ?? [];
        $else = $node['else'] ?? null;

        $this->emit("if ({$condition}) {\n");
        $this->indent++;

        foreach ($body as $subNode) {
            $this->compileNode($subNode);
        }

        $this->indent--;

        // Elseifs
        foreach ($elseifs as $elseif) {
            $elseifCondition = $elseif['condition'];
            $elseifBody = $elseif['body'];

            $this->emit("} elseif ({$elseifCondition}) {\n");
            $this->indent++;

            foreach ($elseifBody as $subNode) {
                $this->compileNode($subNode);
            }

            $this->indent--;
        }

        // Else
        if ($else) {
            $this->emit("} else {\n");
            $this->indent++;

            foreach ($else as $subNode) {
                $this->compileNode($subNode);
            }

            $this->indent--;
        }

        $this->emit("}\n");
    }

    /**
     * Compile foreach loop
     */
    private function compileForeach(array $node): void
    {
        $array = $node['array'];
        $key = $node['key'];
        $value = $node['value'];
        $body = $node['body'] ?? [];

        // Build foreach syntax
        $foreachSyntax = "foreach ({$array} as ";
        if ($key) {
            $foreachSyntax .= "{$key} => {$value}";
        } else {
            $foreachSyntax .= "{$value}";
        }
        $foreachSyntax .= ")";

        $this->emit("{$foreachSyntax} {\n");
        $this->indent++;

        // Create loop variable
        $this->emit("// Loop variable\n");
        $this->emit('$__loops[] = $__loop = (object)[' . "\n");
        $this->indent++;
        $this->emit("'index' => \\Footup\\View\\LoopHelper::getIndex(\$__loops),\n");
        $this->emit("'iteration' => \\Footup\\View\\LoopHelper::getIteration(\$__loops),\n");
        $this->emit("'count' => count({$array}),\n");
        $this->emit("'first' => count(\$__loops) === 1,\n");
        $this->emit("'last' => count(\$__loops) === count({$array}),\n");
        $this->emit("'depth' => count(\$__loops)\n");
        $this->indent--;
        $this->emit("];\n\n");

        // Compile body
        foreach ($body as $subNode) {
            $this->compileNode($subNode);
        }

        // Pop loop
        $this->emit("array_pop(\$__loops);\n");
        $this->emit("\$__loop = end(\$__loops) ?: null;\n");

        $this->indent--;
        $this->emit("}\n");
    }

    /**
     * Compile while loop
     */
    private function compileWhile(array $node): void
    {
        $condition = $node['condition'];
        $body = $node['body'] ?? [];

        $this->emit("while ({$condition}) {\n");
        $this->indent++;

        foreach ($body as $subNode) {
            $this->compileNode($subNode);
        }

        $this->indent--;
        $this->emit("}\n");
    }

    /**
     * Compile for loop
     */
    private function compileFor(array $node): void
    {
        $expr = $node['expr'];
        $body = $node['body'] ?? [];

        $this->emit("for ({$expr}) {\n");
        $this->indent++;

        foreach ($body as $subNode) {
            $this->compileNode($subNode);
        }

        $this->indent--;
        $this->emit("}\n");
    }

    /**
     * Compile block definition
     */
    private function compileBlock(array $node): void
    {
        $name = $node['name'];
        $body = $node['body'] ?? [];

        // Store block in global array
        $this->emit("\$__blocks['" . $name . "'] = function() {\n");
        $this->indent++;

        foreach ($body as $subNode) {
            $this->compileNode($subNode);
        }

        $this->indent--;
        $this->emit("};\n");
    }

    /**
     * Compile layout extension
     */
    private function compileExtends(array $node): void
    {
        $path = $node['path'];
        $this->emit("\$__extends = '" . $path . "';\n");
    }

    /**
     * Compile include
     */
    private function compileInclude(array $node): void
    {
        $path = $node['path'];
        $vars = $node['vars'] ?? '';

        if ($vars) {
            $this->emit("\$__includeVars = {$vars};\n");
            $this->emit("extract(\$__includeVars);\n");
        }

        $this->emit("include \\Footup\\View\\Engine::findTemplate('{$path}');\n");
    }

    /**
     * Compile expression
     */
    private function compileExpression(array $node): void
    {
        $expr = $node['expr'];
        $this->emit("echo {$expr};\n");
    }

    /**
     * Emit code to output
     * 
     * @param string $code Code to emit
     */
    private function emit(string $code): void
    {
        $indentation = str_repeat('    ', $this->indent);
        $lines = explode("\n", $code);

        foreach ($lines as $line) {
            if ($line === '') {
                $this->output .= "\n";
            } else {
                $this->output .= $indentation . $line . "\n";
            }
        }
    }
}
