<?php namespace Nabeghe\Tepade;

/**
 * @method detect()
 * @method static detect(array|string $patterns, ?string $text, ?callable $callback = null, $constraints = null, $args = null, $regex = null)
 */
class Tepade
{
    /**
     * Regex to grab named parameters from a text.
     * Names must start with a letter & only supports of letters, numbers & underscores.
     */
    protected const REGEX = '/{([a-zA-Z][_a-zA-Z\d]*)}/';

    public function __construct(
        public $patterns,
        public ?string $text,
        public ?\Closure $callback = null,
        public ?array $validators = null,
        public ?array $args = [],
        public string $regex = self::REGEX,
    ) {
    }

    public static function new($patterns, ?string $text)
    {
        return new static($patterns, $text);
    }

    public function callback(?callable $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    public function validators(?callable $validators): static
    {
        $this->validators = $validators;
        return $this;
    }

    public function args($args): static
    {
        $this->args = $args;
        return $this;
    }

    protected static function acceptsNull(callable $callable): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable);
        $parameters = $reflection->getParameters();
        if (!isset($parameters[0])) {
            return false;
        }
        $firstParam = $parameters[0];
        if ($firstParam->hasType()) {
            $type = $firstParam->getType();
            return $type->allowsNull();
        }
        return true;
    }

    protected static function _detect(
        $pattern,
        ?string $text,
        ?callable $callback = null,
        ?array $validators = null,
        ?array $args = null,
        ?string $regex = null,
    ): ?array {
        // multiple patterns.
        if (is_array($pattern)) {
            if ($pattern) {
                foreach ($pattern as $_pattern) {
                    $matches = static::detect($_pattern, $text, $callback, $validators, $args, $regex);
                    if ($matches !== null) {
                        return $matches;
                    }
                }
            }
            if ($callback && static::acceptsNull($callback)) {
                $callback(null, $text, ...$args);
            }
            return null;
        }

        // convert pattern to string.
        if (!is_string($pattern)) {
            $pattern = strval($pattern);
        }
        $pattern = str_replace('/', '\/', $pattern);

        if ($args === null) {
            $args = [];
        }

        $replace_rule = function ($matches) use ($validators) {
            $param_name = $matches[1];
            $constraint = $validators[$param_name] ?? '.*';
            return sprintf("(?<%s>%s?)", $param_name, $constraint);
        };

        $regex ??= static::REGEX;
        $regex = '/^'.preg_replace_callback($regex, $replace_rule, $pattern).'$/mu';

        $matched = (bool) preg_match($regex, $text, $matches, PREG_UNMATCHED_AS_NULL);
        if ($matched) {
            array_walk($matches, fn(&$x) => $x = ($x === '' ? null : $x));
            array_shift($matches);
            if ($callback) {
                $callback(is_array($matches) ? $matches : [], $text, ...$args);
            }
            return $matches;
        }

        if ($callback && static::acceptsNull($callback)) {
            $callback(null, $text, ...$args);
        }
        return null;
    }

    public static function structure(array $structure, ?string $text, ?array $args = null): bool
    {
        foreach ($structure as $class => $substructure) {
            if (is_int($class)) {
                $matches = self::detect(
                    $substructure[0] ?? null,
                    $text,
                    $substructure[1] ?? null,
                    $substructure[2] ?? null,
                    $args,
                );
            } else {
                $matches = self::detect(
                    $substructure[0] ?? null,
                    $text,
                    [$class, 'on'],
                    $substructure[1] ?? null,
                    $args,
                );
            }
            if ($matches !== null) {
                return true;
            }
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        if ($name == 'detect') {
            return static::_detect($this->patterns, $this->text, $this->callback, $this->validators, $this->args,
                $this->regex);
        }

        throw new \BadMethodCallException("The method '$name' does not exist.");
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'detect') {
            if (!isset($arguments[0]) || !isset($arguments[1])) {
                throw new \InvalidArgumentException("Not enough arguments provided for method '$name'.");
            }
            if (!is_array($arguments[0]) && !is_string($arguments[0])) {
                throw new \TypeError("The first argument of the static method '$name' must be string or array.");
            }
            if (isset($arguments[2]) && !is_callable($arguments[2])) {
                throw new \TypeError("The third argument of the static method '$name' must be callable.");
            }
            if (isset($arguments[3]) && !is_array($arguments[3])) {
                throw new \TypeError("The fourth argument of the static method '$name' must be array or null.");
            }
            if (isset($arguments[4]) && !is_array($arguments[4])) {
                throw new \TypeError("The fifth argument of the static method '$name' must be array or null.");
            }
            if (isset($arguments[5]) && !is_array($arguments[5])) {
                throw new \TypeError("The sixth argument of the static method '$name' must be string or null.");
            }
            return static::_detect(
                $arguments[0],
                strval($arguments[1]),
                $arguments[2] ?? null,
                $arguments[3] ?? null,
                $arguments[4] ?? null,
                $arguments[5] ?? null,
            );
        }

        throw new \BadMethodCallException("The static  method '$name' does not exist.");
    }
}