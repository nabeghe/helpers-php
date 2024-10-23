<?php namespace Nabeghe\Stringer;

/**
 * Stringer class.
 *
 * Holds a string within itself & can access the methods of the {@see Str} class with the same name.
 * Most methods apply changes to the original value and return the {@see Stringer} object.
 *
 * @method self after(string $search)
 * @method self afterLast(string $search)
 * @method self before(string $search)
 * @method self beforeLast(string $search)
 * @method self between(string $from, string $to)
 * @method bool bool(string|null $value)
 * @method self camel(string|null $value)
 * @method string[] chars(string $text)
 * @method string charAt(int $index)
 * @method self chopStart(string $needle, bool $ignoreCase = false)
 * @method self chopEnd(string $needle, bool $ignoreCase = false)
 * @method string[] chunk(int $limit)
 * @method bool contains(string|string[] $needles, bool $ignoreCase = false)
 * @method bool containsAll(string[] $needles, bool $ignoreCase = false)
 * @method bool doesntContain(string|string[] $needles, bool $ignoreCase = false)
 * @method self convertCase(int $mode = MB_CASE_FOLD, ?string $encoding = 'UTF-8')
 * @method self def(?string $def = '')
 * @method self deduplicate(string $character = ' ')
 * @method bool endsWith(string $haystack, string $needle)
 * @method self escape(string[] $chars)
 * @method self escapeMarkdown()
 * @method self escapeMarkdownCode()
 * @method self escapeMarkdownV2()
 * @method self escapeMarkdownV2CodeBlock()
 * @method string[] explode(string $separator)
 * @method self finish(string $cap)
 * @method self firstWord(string $wordsSeperator = ' ')
 * @method bool hasChar(string|string[] $chars)
 * @method int|false ipos(string $needle, int $offset = 0)
 * @method bool isMadeOf(string|string[] $chars)
 * @method bool string|string[] $pattern)
 * @method bool isUrl(string[] $protocols = [])
 * @method bool isUuid()
 * @method self kebab()
 * @method int len(string|null $encoding = null, bool $emojible = true)
 * @method self lcfirst()
 * @method self limit(int $limit, string $extra = '...')
 * @method self limitWords(int $words = 100, string $end = '...')
 * @method string[] lines()
 * @method self lower()
 * @method int longestSequence(string $char = ' ')
 * @method self mask(string $character, int $index, int|null $length = null, string $encoding = 'UTF-8')
 * @method self normalizeNumbers()
 * @method self normalizeArabicNumbers()
 * @method self normalizePersianNumbers()
 * @method self normalizePersianChars(bool $all = true)
 * @method self normalizeWhitespace()
 * @method self numbers()
 * @method self padBoth(int $length, string $pad = ' ')
 * @method self padLeft(int $length, string $pad = ' ')
 * @method self padRight(int $length, string $pad = ' ')
 * @method array<int, string|null> parseCallback($callback, $default = null)
 * @method array<int, string|null> pos(string $haystack, string $needle, int $offset = 0)
 * @method array readingTime()
 * @method self replaceDeeply(string|array $search)
 * @method self reverse()
 * @method self snake(string $delimiter = '_')
 * @method self start(string $prefix)
 * @method self sub(int $start, int|null $length = null, string $encoding = 'UTF-8')
 * @method string[] split(int $length)
 * @method self squish()
 * @method bool startsWith(string $needle)
 * @method self studly()
 * @method self take(int $limit)
 * @method self toBase64()
 * @method self trim(string|null $charlist = null)
 * @method self ltrim(string|null $charlist = null)
 * @method self rtrim(string|null $charlist = null)
 * @method self title()
 * @method self headline()
 * @method self apa()
 * @method self ucfirst()
 * @method string[] ucsplit()
 * @method self upper()
 * @method int wordCount(string|null $characters = null)
 * @method self wordWrap(int $characters = 75, string $break = "\n", bool $cutLongWords = false)
 * @method self wrap(string $before, string|null $after = null)
 * @method self unwrap(string $value, string $before, string|null $after = null)
 */
class Stringer
{
    protected string $value;

    public const RETURNS = [
        'charAt',
    ];

    /**
     * Constructor.
     *
     * @param  string  $value  Raw string.
     */
    public function __construct($value)
    {
        $this->value = Str::tryCast($value);
    }

    /**
     * Creates a `Stringer` object from a base64 string by decoding it.
     *
     * @param  string  $string
     * @param  bool  $strict
     * @return static
     */
    public static function fromBase64($string, $strict = false)
    {
        $decoded = base64_decode($string, $strict);
        if ($decoded === false) {
            return null;
        }
        return new static($decoded);
    }

    /**
     * Generates a random string and creates a Stringer object with it.
     *
     * @param  int  $length
     * @param  string  $source
     * @return string
     */
    public function fromRandom($length, $source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        return Str::random($length, $this->value);
    }

    public function is($pattern)
    {
        return Str::is($pattern, $this->value);
    }

    public function isJson(&$decoded = null)
    {
        return Str::isJson($this->value, $decoded);
    }

    public function match($pattern)
    {
        return Str::match($pattern, $this->value);
    }

    public function isMatch($pattern)
    {
        return Str::isMatch($pattern, $this->value);
    }

    public function replace($search, $replace, $caseSensitive = true)
    {
        $this->value = Str::replace($search, $replace, $this->value, $caseSensitive);
        return $this;
    }

    public function replaceFirst($search, $replace)
    {
        $this->value = Str::replaceFirst($search, $replace, $this->value);
        return $this;
    }

    public function replaceStart($search, $replace)
    {
        $this->value = Str::replaceStart($search, $replace, $this->value);
        return $this;
    }

    public function replaceLast($search, $replace)
    {
        $this->value = Str::replaceLast($search, $replace, $this->value);
        return $this;
    }

    public function replaceEnd($search, $replace)
    {
        $this->value = Str::replaceLast($search, $replace, $this->value);
        return $this;
    }

    public function replaceMatches($pattern, $replace, $subject, $limit = -1)
    {
        $this->value = Str::replaceMatches($pattern, $replace, $this->value, $limit);
        return $this;
    }

    public function replaceFirstLetters($needle, $replace)
    {
        $this->value = Str::replaceFirstLetters($needle, $replace, $this->value);
        return $this;
    }

    public function replaceOnce($needle, $replace)
    {
        $this->value = Str::replaceOnce($needle, $replace, $this->value);
        return $this;
    }

    public function remove($search, $ignoreCase = false)
    {
        $this->value = Str::remove($search, $this->value, $ignoreCase);
        return $this;
    }

    public function swap($map)
    {
        $this->value = Str::swap($map, $this->value);
        return $this;
    }

    /**
     * Generates a random string and creates a Stringer object with it.
     *
     * @param  int  $length
     * @return string
     */
    public function random($length)
    {
        return Str::random($length, $this->value);
    }

    public function __toString()
    {
        return $this->value;
    }

    public function __call($name, $arguments)
    {
        $result = Str::$name($this->value, ...$arguments);

        if (is_string($result) || $result === null) {
            if (in_array($name, self::RETURNS)) {
                return $result;
            }
            $this->value = $result === null ? '' : $result;
            return $this;
        }

        return $result;
    }
}