<?php namespace Nabeghe\Cally;

use Throwable;

/**
 * Just a handy helper for callables and calling them!
 */
class Cally
{
    /**
     * Invokes a series of callbacks sequentially and in order.
     *
     * @param  iterable<callable>  $callbacks
     * @param  mixed  ...$args
     * @return void
     */
    public static function action($callbacks, ...$args)
    {
        if (!is_array($callbacks)) {
            $callbacks = [$callbacks];
        }

        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $callback(...$args);
            }
        }
    }

    /**
     * An alternative to {@see call_user_func_array()} with the difference that in the callback array,
     *  arguments can be inserted starting from the third item onward.
     *
     * @param  callable|array  $callback  The callback.
     *                                    If it's an array, the first & second items are considered as the callback;
     *                                    And The remaining items are treated as input arguments.
     * @param  mixed  ...$args  Optional arguments that are passed to the callback.
     *                          They take priority over the arguments in the array (previous input).
     * @return mixed
     */
    public static function call($callback, ...$args)
    {
        if (is_callable($callback)) {
            return $callback(...$args);
        }

        if (is_array($callback)) {
            $real_callback = array_slice($callback, 0, 2);
            if (is_callable($real_callback)) {
                return call_user_func_array(
                    array_slice($callback, 0, 2),
                    array_merge($args, array_slice($callback, 2))
                );
            }
        }

        return null;
    }

    /**
     * Sequentially passes a value through a series of callbacks, updating it with each callback's output, and returns the final value.
     *
     * @param  mixed  $value  The filtered value.
     * @param $callbacks
     * @param ...$args
     * @return mixed
     */
    public static function filter($callbacks, $value, ...$args)
    {
        if (!is_array($callbacks)) {
            $callbacks = [$callbacks];
        }

        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $value = $callback($value, ...$args);
            }
        }

        return $value;
    }

    /**
     * Executes a callable between `ob_start`, `ob_get_contents`, & `ob_end_clean`, and returns the final buffer.
     *
     * @param  callable  $callback
     * @return string
     */
    public static function ob($callback): ?string
    {
        ob_start();
        $callback();
        $output = ob_get_contents();
        ob_end_clean();
        return is_string($output) ? $output : null;
    }

    /**
     * Call the given Closure with the given value then return the value.
     *
     * @template TValue
     *
     * @param  TValue  $value
     * @param  (callable(TValue): mixed)|null  $callback
     * @return TValue|null
     * @copyright laravel
     */
    public static function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return null;
        }

        $callback($value);

        return $value;
    }

    /**
     * Executes a callable between try and catch.
     *
     * @param  callable  $callable  The main callable.
     * @param  callable  $finally  The finally callable.
     * @param  Throwable|null  $error
     * @return bool
     */
    public static function try($callable, $finally = null, ?Throwable &$error = null): bool
    {
        try {
            static::call($callable);
            return true;
        } catch (Throwable $e) {
            $error = $e;
        } finally {
            if ($finally) {
                static::call($finally);
            }
        }
        return false;
    }

    /**
     * Returna the default value of the given value.
     *
     * @template TValue
     * @template TArgs
     *
     * @param  TValue|Closure(TArgs): TValue  $value
     * @param  TArgs  ...$args
     * @return TValue
     * @copyright laravel
     */
    public static function value($value, ...$args)
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }

    /**
     * Makes a callable that returns the specified value.
     *
     * @param  mixed  $value  The value that will returns from the callback.
     */
    public static function valback($value)
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Returns the given value, optionally passed through the given callback.
     *
     * @template TValue
     * @template TReturn
     *
     * @param  TValue  $value
     * @param  (callable(TValue): (TReturn))|null  $callback
     * @return ($callback is null ? TValue : TReturn)
     * @copyright laravel
     */
    public static function with($value, ?callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}