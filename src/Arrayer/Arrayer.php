<?php namespace Nabeghe\Arrayer;

/**
 * @method self add(string|int|float $key, mixed $value)
 * @method self collapse()
 * @method self crossJoin(...$arrays)
 * @method self divide()
 * @method self dot(string $prepend = '')
 * @method self undot()
 * @method self except(array|string|int|float $keys)
 * @method bool exists(string|int|float $key)
 * @method mixed first(?callable $callback = null, $default = null)
 * @method string implode(?array $options = null)
 * @method bool includes(iterable|mixed $needle, bool $strict = false)
 * @method bool includesAny(iterable|mixed $needle, bool $strict = false)
 * @method bool isZeroBasedIndex()
 * @method mixed last(?callable $callback = null, $default = null)
 * @method self merge(array ...$arrays)
 * @method self mergeTwo(array $arr2)
 * @method self take(int $limit)
 * @method self flatten(int $depth = INF)
 * @method mixed get(string|int|null $key, mixed $default = null)
 * @method bool has(string|array $keys)
 * @method bool hasAll(\ArrayAccess|array $array, string|array $keys)
 * @method bool hasAny(string|array $keys)
 * @method bool isAssoc()
 * @method bool isList()
 * @method string join(string $glue, string $finalGlue = '')
 * @method self prependKeysWith(string $prependWith)
 * @method self only(array|string $keys)
 * @method self select(array|string $keys)
 * @method self explodePluckParameters(string|array|null $key)
 * @method self map(callable $callback)
 * @method self mapWithKeys(callable $callback)
 * @method self mapSpread(callable $callback)
 * @method string pregReplace(array $replacements, $subject)
 * @method self prepend(mixed $value, mixed $key = null)
 * @method string query()
 * @method mixed random(int|null $number = null, bool $preserveKeys = false)
 * @method self shuffle()
 * @method self sortRecursive(int $options = SORT_REGULAR, bool $descending = false)
 * @method self sortRecursiveDesc(int $options = SORT_REGULAR)
 * @method string toCssClasses()
 * @method string toCssStyles()
 * @method self where(callable $callback)
 * @method self whereNotNull()
 */
class Arrayer implements \ArrayAccess, \JsonSerializable
{
    public array $data;

    public const RETURNS = ['first', 'last', 'pull', 'random'];

    public const ARR_CLASS = Arr::class;

    public function __construct($value = null)
    {
        $arr_class = static::ARR_CLASS;
        $this->data = $arr_class::cast($value);
    }

    public static function wrap($value)
    {
        $arr_class = static::ARR_CLASS;
        return new static($arr_class::wrap($value));
    }

    public static function wrapEasy($value)
    {
        $arr_class = static::ARR_CLASS;
        return new static($arr_class::wrapEasy($value));
    }

    public static function wrapForce($value)
    {
        $arr_class = static::ARR_CLASS;
        return new static($arr_class::wrapEasy($value));
    }

    public function assign($key, $value)
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function forget($keys)
    {
        $arr_class = static::ARR_CLASS;
        $arr_class::forget($this->data, $keys);
        return $this;
    }

    public function pull($key, $default = null)
    {
        $arr_class = static::ARR_CLASS;
        $arr_class::pull($this->data, $key, $default);
        return $this;
    }

    public function remove(&$data, $values)
    {
        $arr_class = static::ARR_CLASS;
        $arr_class::remove($this->data, $values);
        return $this;
    }

    public function set(&$array, $key, $value)
    {
        $arr_class = static::ARR_CLASS;
        $arr_class::set($this->data, $key, $value);
        return $this;
    }

    public function &__get(string $name)
    {
        return $this->data[$name];
    }

    public function __set(string $name, $value): void
    {
        $this->data[$value] = $value;
    }

    public function __call($name, $arguments)
    {
        $arr_class = static::ARR_CLASS;
        $result = $arr_class::$name($this->data, ...$arguments);

        if (is_array($result) || $result === null) {
            if (in_array($name, self::RETURNS)) {
                return $result;
            }
            $this->data = $result === null ? '' : $result;
            return $this;
        }

        return $result;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        return $this->data[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }
}