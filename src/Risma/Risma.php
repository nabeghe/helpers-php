<?php namespace Nabeghe\Risma;

use Exception;
use Throwable;

/**
 * Risma
 *  A lightweight and flexible string processing engine for PHP.
 *  eveloped by https://github.com/nabeghe.
 */
class Risma
{
    /**
     * @var array Custom functions registry.
     */
    protected array $funcs = [];

    /**
     * @var array Registered classes for method resolution.
     */
    protected array $classes = [];

    public function __construct()
    {
        $this->defineDefaultFuncs();
    }

    /**
     * Defines built-in default functions.
     */
    protected function defineDefaultFuncs(): void
    {
        $this->funcs['exists'] ??= function ($value) {
            return ($value === null || $value === '') ? '0' : '1';
        };

        $this->funcs['ok'] ??= function ($value) {
            return $value ? '1' : '0';
        };
    }

    /**
     * Register a custom function.
     * * @param  string  $name  The name used in the template.
     * @param  callable  $callback  The logic to execute.
     */
    public function addFunc(string $name, callable $callback): void
    {
        $this->funcs[$name] = $callback;
    }

    /**
     * Register a class to expose its methods to the engine.
     * * @param  string  $className  Full namespace of the class.
     */
    public function addClass(string $className): void
    {
        if (class_exists($className)) {
            $this->classes[] = $className;
        }
    }

    /**
     * Renders the template string by replacing placeholders.
     * * @param  string  $text  The raw string with placeholders like {var.func}.
     * @param  array  $vars  Key-value pairs of data.
     * @param  bool  $default  If true, returns empty string for missing variables.
     * @return string The processed text.
     * @throws Exception
     */
    public function render(string $text, array $vars, bool $default = true): string
    {
        $pattern = '/(!?)\{\s*(.*?)\s*\}/s';

        return preg_replace_callback($pattern, function ($matches) use ($vars, $default) {
            $isEscaped = !empty($matches[1]);
            $content = $matches[2];

            if ($isEscaped) {
                return '{'.$content.'}';
            }

            try {
                return $this->processExpression($content, $vars, $default);
            } catch (Throwable $e) {
                // Modified logic: If default is false, re-throw the exception for PHPUnit
                if (!$default) {
                    throw $e;
                }
                return '';
            }
        }, $text);
    }

    /**
     * Processes the content inside a single {} block.
     * * @throws Exception
     */
    protected function processExpression(string $expression, array $vars, bool $default): string
    {
        // Split the chain while respecting parentheses and quotes
        $chain = $this->splitChain($expression);

        if (empty($chain)) {
            return '';
        }

        $head = array_shift($chain);
        $value = null;

        // Check for direct function call (starts with @)
        if (substr($head, 0, 1) === '@') {
            $funcName = substr($head, 1);
            $value = $this->executeFunction($funcName, null, true);
        } else {
            // Handle variable replacement
            if (array_key_exists($head, $vars)) {
                $value = $vars[$head];
            } else {
                if ($default) {
                    $value = '';
                } else {
                    throw new Exception("Variable '$head' is undefined.");
                }
            }
        }

        // Process the rest of the function chain
        foreach ($chain as $item) {
            $value = $this->executeFunction($item, $value, false);
        }

        return (string) $value;
    }

    /**
     * Executes a single function within the chain.
     * * @param  string  $token  The function part, e.g., "func1" or "func('arg')".
     * @param  mixed  $prevValue  The value from the previous step in the chain.
     * @param  bool  $isFirstCall  Whether this is the start of a direct @ call.
     * @throws Exception
     */
    protected function executeFunction(string $token, $prevValue, bool $isFirstCall)
    {
        if (preg_match('/^([a-zA-Z0-9_\\\:]+)(?:\((.*)\))?$/s', $token, $matches)) {
            $funcName = $matches[1];
            $argsString = $matches[2] ?? null;

            $callable = $this->resolveCallback($funcName);
            $args = [];

            if ($argsString !== null && trim($argsString) !== '') {
                try {
                    $code = "return [$argsString];";
                    $args = eval($code);
                } catch (Throwable $e) {
                    throw new Exception("Error parsing arguments for '$funcName'.");
                }
            }

            if (!$isFirstCall) {
                // Check if user specified where the piped value should go using '$'
                $placeholderIndex = array_search('$', $args, true);

                if ($placeholderIndex !== false) {
                    // Replace '$' with the actual previous value
                    $args[$placeholderIndex] = $prevValue;
                } else {
                    // Default behavior: prepend to arguments
                    array_unshift($args, $prevValue);
                }
            }

            return call_user_func_array($callable, $args);
        }

        throw new Exception("Invalid syntax: $token");
    }

    /**
     * Resolves the function name to a callable (Custom -> Class -> Global).
     * * @throws Exception
     */
    protected function resolveCallback(string $name): callable
    {
        // 1. Check custom registered functions
        if (isset($this->funcs[$name])) {
            return $this->funcs[$name];
        }

        // 2. Check registered class methods
        foreach ($this->classes as $class) {
            if (method_exists($class, $name)) {
                return [$class, $name];
            }
        }

        // 3. Check global PHP functions
        if (function_exists($name)) {
            return $name;
        }

        throw new Exception("Function or method '$name' not found.");
    }

    /**
     * Splits the chain by dot while ignoring dots inside quotes or parentheses.
     */
    protected function splitChain(string $str): array
    {
        $parts = [];
        $buffer = '';
        $stack = 0; // Parentheses stack
        $inQuote = false;
        $quoteChar = '';

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $char = $str[$i];

            // Quote management
            if (($char === '"' || $char === "'") && ($i === 0 || $str[$i - 1] !== '\\')) {
                if (!$inQuote) {
                    $inQuote = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuote = false;
                }
            }

            // Parentheses management (only if not inside quotes)
            if (!$inQuote) {
                if ($char === '(') {
                    $stack++;
                }
                if ($char === ')') {
                    $stack--;
                }
            }

            // Split by dot only at the root level (not in quotes/parentheses)
            if ($char === '.' && $stack === 0 && !$inQuote) {
                $parts[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        if ($buffer !== '') {
            $parts[] = trim($buffer);
        }

        return $parts;
    }
}
