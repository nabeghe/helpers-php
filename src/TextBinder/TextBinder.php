<?php namespace Nabeghe\TextBinder;

/**
 * Lightweight text binding engine with function chaining support.
 *
 * Allows replacing placeholders inside text using variables
 * and applying chained functions on their values.
 */
class TextBinder
{
    /**
     * Registered functions available for placeholder processing.
     *
     * @var array<string, callable>
     */
    protected array $functions;

    /**
     * Initialize the binder and register default functions.
     */
    public function __construct()
    {
        $this->defineFunctions();
    }

    /**
     * Define built-in placeholder functions.
     *
     * @return void
     */
    protected function defineFunctions(): void
    {
        $this->functions['exists'] ??= function ($value) {
            return $value == '' ? '0' : '1';
        };

        $this->functions['ok'] ??= function ($value) {
            return $value ? '1' : '0';
        };
    }

    /**
     * Register a custom function for use in placeholders.
     *
     * @param string   $name Function identifier
     * @param callable $func Function handler
     * @return void
     */
    public function addFunc(string $name, callable $func): void
    {
        $this->functions[$name] = $func;
    }

    /**
     * Check if a function is registered.
     *
     * @param string $name Function identifier
     * @return bool
     */
    public function hasFunc(string $name): bool
    {
        return isset($this->functions[$name]);
    }

    /**
     * Remove a registered function.
     *
     * @param string $name Function identifier
     * @return bool True if removed, false otherwise
     */
    public function delFunc(string $name): bool
    {
        if (isset($this->functions[$name])) {
            unset($this->functions[$name]);
            return true;
        }

        return false;
    }

    /**
     * Render text by replacing placeholders with variables
     * and applying chained functions.
     *
     * @param string $text     Input text
     * @param array  $vars     Variables for binding
     * @param bool   $default  Replace missing variables with empty string
     * @return string
     */
    public function render(string $text, array $vars, bool $default = true): string
    {
        if (!$vars) {
            return $text;
        }

        // Pattern for single braces with function support: {name} or {name.func1.func2}
        $text = preg_replace_callback(
            '/\{\s*([a-zA-Z0-9_-]+(?:\.[^.\s}]+)*)\s*}/',
            function ($matches) use ($vars, $default) {
                $parts = explode('.', $matches[1]);
                $key = array_shift($parts);

                if (isset($vars[$key])) {
                    $value = $vars[$key];
                } elseif ($default) {
                    $value = '';
                } else {
                    return $matches[0];
                }

                // Apply functions in chain
                foreach ($parts as $func) {
                    $handle = false;

                    if (isset($this->functions[$func])) {
                        $value = $this->functions[$func]($value);
                        $handle = true;
                    } elseif (function_exists($func)) {
                        $value = $func($value);
                        $handle = true;
                    }

                    if ($handle) {
                        $value = $this->handleFunctionOutput($value);
                    }
                }

                return $value;
            },
            $text
        );

        return $text;
    }

    /**
     * Normalize function output before further processing.
     *
     * @param mixed $value
     * @return string
     */
    protected function handleFunctionOutput($value): string
    {
        // Convert to string if it's a boolean or null
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_null($value)) {
            return '';
        }

        // Convert arrays/objects to string representation
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
