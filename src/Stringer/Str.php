<?php namespace Nabeghe\Stringer;

use JsonException;
use Throwable;

/**
 * String utils.
 * Norice: Some Str methods related to Laravel have also been used in this class. However, some of them have been modified.
 */
class Str
{
    /**
     * The cache of snake-cased words.
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Returns the remainder of a string after the first occurrence of a given value.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Returns the remainder of a string after the last occurrence of a given value.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function afterLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Gets the portion of a string before the first occurrence of a given value.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    /**
     * Gets the portion of a string before the last occurrence of a given value.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return static::sub($subject, 0, $pos);
    }

    /**
     * Get the portion of a string between two given values.
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * @return string
     */
    public static function between($subject, $from, $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Gets the smallest possible portion of a string between two given values.
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * @return string
     */
    public static function betweenFirst($subject, $from, $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::before(static::after($subject, $from), $to);
    }

    /**
     * Intelligently converts a string to `bool`:
     * Non-zero numbers are equal to `true`, and certain texts are also considered `true`, while others are `false`.
     *
     * @param  string|null  $value
     * @return bool
     */
    public static function bool($value)
    {
        if ($value === null) {
            return false;
        }
        if (is_numeric($value) && (float) $value > 0) {
            return true;
        }
        return in_array(
            $value,
            ['1', 'yes', 'true', 'ok', 'yep', 'yea', 'yeah', 'right', 'correct', 'valid', 'sure', 'fine'],
        );
    }

    /**
     * Converts a value to camel case.
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Converts any value to a string by casting.
     * @param  mixed  $value  Something that is going to be cast.
     * @param  string|null  $default  Optional. Default value.
     */
    public static function cast($value, $default = null)
    {
        try {
            if (is_string($value)) {
                return $value;
            }
            return (string) $value;
        } catch (Throwable $e) {
            if (is_object($value)) {
                return self::fromEnum($value);
            }
            return $default;
        }
    }

    /**
     * Converts any value to a string by casting.
     * It returns the default when throwabed an exception.
     * @param  mixed  $value  The value to be casted.
     * @param  string|null  $default  Optional. Default value.
     * @return string|null The casted value.
     */
    public static function tryCast($value, $default = null)
    {
        try {
            return self::cast($value, $default);
        } catch (Throwable $e) {
            return $default;
        }
    }

    /**
     * @param  string  $text
     * @return string[]
     */
    public static function chars($text)
    {
        $len = static::len($text);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            $result[] = static::sub($text, $i, 1);
        }
        return $result;
    }

    /**
     * Gets the character at the specified index.
     * @param  string  $subject
     * @param  int  $index
     * @return string|false
     */
    public static function charAt($subject, $index)
    {
        $length = static::len($subject);

        if ($index < 0 ? $index < -$length : $index > $length - 1) {
            return false;
        }

        return static::sub($subject, $index, 1);
    }

    /**
     * Removes the given string(s) if it exists at the start of the haystack.
     * @param  string  $subject
     * @param  string|array  $needle
     * @param  bool  $ignoreCase
     * @return string
     */
    public static function chopStart($subject, $needle, $ignoreCase = false)
    {
        if (is_array($needle)) {
            $needle = implode('', $needle);
        }

        $subject_length = static::len($subject);
        $needle_length = static::len($needle);
        if ($subject_length == 0 || $needle_length == 0) {
            return $subject;
        }

        $function_str_pos = !$ignoreCase ? [static::class, 'pos'] : [static::class, 'ipos'];
        if ($function_str_pos($subject, $needle) === 0) {
            $subject = static::sub($subject, $needle_length);
        }
        if (!is_string($subject)) {
            return '';
        }
        return $subject;
    }

    /**
     * Removes the given string(s) if it exists at the end of the haystack.
     * @param  string  $subject
     * @param  string|array  $needle
     * @param  bool  $ignoreCase
     * @return string
     */
    public static function chopEnd($subject, $needle, $ignoreCase = false)
    {
        if (is_array($needle)) {
            $needle = implode('', $needle);
        }

        $subject_length = static::len($subject);
        $needle_length = static::len($needle);
        if ($subject_length == 0 || $needle_length == 0) {
            return $subject;
        }

        $function_str_pos = !$ignoreCase ? [static::class, 'pos'] : [static::class, 'ipos'];
        if ($function_str_pos($subject, $needle, $subject_length - $needle_length) !== false) {
            $subject = static::sub($subject, 0, -$needle_length);
        }

        if (!is_string($subject)) {
            return '';
        }
        return $subject;
    }

    /**
     * Chunks a string to an array.
     * @param  string  $string  The text to be chunked.
     * @param  int  $limit  The limit or max length of each chunk.
     * @return string[] Chunked string.
     */
    public static function chunk($string, $limit)
    {
        $chunks = [];
        $length = self::len($string);
        for ($i = 0; $i < $length; $i += $limit) {
            $chunks[] = self::sub($string, $i, $limit);
        }
        return $chunks;
    }

    /**
     * Determines if a given string contains a given substring.
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function contains($haystack, $needles, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack);
        }

        if (!is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($ignoreCase) {
                $needle = mb_strtolower($needle);
            }

            if ($needle !== '' && (false !== static::pos($haystack, $needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if a given string contains all array values.
     * @param  string  $haystack
     * @param  iterable<string>  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function containsAll($haystack, $needles, $ignoreCase = false)
    {
        foreach ($needles as $needle) {
            if (!static::contains($haystack, $needle, $ignoreCase)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if a given string doesn't contain a given substring.
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function doesntContain($haystack, $needles, $ignoreCase = false)
    {
        return !static::contains($haystack, $needles, $ignoreCase);
    }

    /**
     * Converts the case of a string.
     * @param  string  $string
     * @param  int  $mode
     * @param  string|null  $encoding
     * @return string
     */
    public static function convertCase($string, $mode = MB_CASE_FOLD, $encoding = 'UTF-8')
    {
        return mb_convert_case($string, $mode, $encoding);
    }

    public static function def($text, $def = '')
    {
        return $text != '' ? $text : $def;
    }

    /**
     * Replaces consecutive instances of a given character with a single character in the given string.
     * @param  string  $string
     * @param  string  $character
     * @return string
     */
    public static function deduplicate($string, $character = ' ')
    {
        return preg_replace('/'.preg_quote($character, '/').'+/u', $character, $string);
    }

    /**
     * Determines if a given string ends with a given substring.
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return '' === $needle || ('' !== $haystack && 0 === substr_compare($haystack, $needle, -static::len($needle)));
    }

    /**
     *
     * @param  string  $string
     * @param  array  $chars
     * @return string
     */
    public static function escape($string, $chars)
    {
        foreach ($chars as $char) {
            $string = preg_replace_callback(
                '/(?<!\\\\)('.preg_quote($char, '/').')/',
                function ($matches) {
                    return '\\'.$matches[0];
                },
                $string
            );
        }
        return $string;
    }

    /**
     * Escapes string for markdown.
     * @param  string  $string
     * @return string
     */
    public static function escapeMarkdown($string)
    {
        return static::escape($string, [
            '`', '*', '_', '{', '}', '[', ']', '(', ')', '#', '+', '-', '!', '>', '|',
        ]);
    }

    /**
     * Escapes string for markdown code section.
     * @param  string  $string
     * @return string
     */
    public static function escapeMarkdownCode($string)
    {
        return static::escape($string, ['`']);
    }

    /**
     * Escapes string for markdown.
     * @param  string  $string
     * @return string
     */
    public static function escapeMarkdownV2($string)
    {
        return static::escape($string, [
            '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!',
        ]);
    }

    /**
     * Escapes string for markdown codeblock.
     * @param  string  $string
     * @return string
     */
    public static function escapeMarkdownV2CodeBlock($string)
    {
        return static::escape($string, ['```']);
    }

    /**
     * @param $string
     * @param $separator
     * @return string[]
     */
    public static function explode($separator, $string)
    {
        return array_filter(explode($separator, $string), function ($line) {
            return static::trim($line) !== '';
        });
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');
        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * Gets the first word.
     * @param  string  $string  The text.
     * @param  string  $wordsSeperator
     * @return string
     */
    public static function firstWord($string, $wordsSeperator = ' ')
    {
        $word_seperator_pos = strpos($string, $wordsSeperator);
        if ($word_seperator_pos !== false) {
            return substr($string, 0, $word_seperator_pos);
        }
        return $string;
    }

    /**
     * Converts an enum to a string.
     * @param  mixed  $enum  Enum value.
     * @param  string|null  $enumType  Optional. Enum class type. Default everything.
     * @param  string|null  $default  Optional. Default type. Default Null.
     * @return string|null Converted value.
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function fromEnum($enum, $enumType = null, $default = null)
    {
        if (is_object($enum)) {
            try {
                if (PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 1) {
                    $reflection = new \ReflectionClass($enum);
                    if (!$reflection->isEnum()) {
                        return $default;
                    }
                    if ($enumType) {
                        $reflection = new \ReflectionClass($enumType);
                        if (!$reflection->isEnum()) {
                            return $default;
                        }
                    }
                }
                return (string) $enum->value;
            } catch (Throwable $e) {
            }
        }
        return $default;
    }

    /**
     * Converts an enum to a string but it also supportes primitive types.
     * Primitive types are converted to a string, but the object type must be an enum.
     * @param  mixed  $enum  Enum value or primitive types.
     * @param  string|null  $enumType  Optional. Name of enum class. Default null.
     * @param  string|null  $default  Optional. Default value. Default null.
     * @return string Converted value.
     */
    public static function maybeFromEnum($enum, ?string $enumType = null, ?string $default = null)
    {
        if (!is_object($enum)) {
            return (string) $enum;
        }
        return self::fromEnum($enum, $enumType, $default);
    }

    /**
     * @param $string
     * @param $chars
     * @return bool
     */
    public static function hasChar($string, $chars)
    {
        $string = static::chars($string);
        if (is_string($chars)) {
            $chars = static::chars($chars);
        }
        if (!$chars) {
            return true;
        }
        foreach ($chars as $character) {
            if (!in_array($character, $string)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Finds position of first occurrence of a case-insensitive string
     * @param  string  $haystack
     * @param  string  $needle
     * @param  int  $offset
     * @return int|false
     */
    public static function ipos($haystack, $needle, $offset = 0)
    {
        $haystack16 = mb_convert_encoding($haystack, 'UTF-16');
        $needle16 = mb_convert_encoding($needle, 'UTF-16');
        $position = stripos($haystack16, $needle16, $offset << 1);
        return $position === false ? false : ($position >> 1);
    }

    /**
     * @param  string|iterable<string>  $string
     * @param  string|iterable<string>  $chars
     * @return bool
     */
    public static function isMadeOf($string, $chars)
    {
        $string_chars = static::chars($string);
        if (is_string($chars)) {
            $chars = static::chars($chars);
        }
        foreach ($string_chars as $string_char) {
            if (!in_array($string_char, $chars)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|iterable<string>  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $value = (string) $value;

        if (!is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $_pattern) {
            $_pattern = (string) $_pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($_pattern === $value) {
                return true;
            }

            $_pattern = preg_quote($_pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $_pattern = str_replace('\*', '.*', $_pattern);

            if (preg_match('#^'.$_pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if a given value is valid JSON.
     * @param  mixed  $value
     * @param  ?array  $decoded
     * @return bool
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public static function isJson($value, &$decoded = null)
    {
        if (!is_string($value)) {
            return false;
        }

        if (function_exists('json_validate')) {
            return json_validate($value, 512);
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }

        return true;
    }

    /**
     * Determine if a given value is a valid URL.
     *
     * @param  mixed  $value
     * @param  string[]  $protocols
     * @return bool
     */
    public static function isUrl($value, $protocols = [])
    {
        if (!is_string($value)) {
            return false;
        }

        $protocolList = empty($protocols)
            ? 'aaa|aaas|about|acap|acct|acd|acr|adiumxtra|adt|afp|afs|aim|amss|android|appdata|apt|ark|attachment|aw|barion|beshare|bitcoin|bitcoincash|blob|bolo|browserext|calculator|callto|cap|cast|casts|chrome|chrome-extension|cid|coap|coap\+tcp|coap\+ws|coaps|coaps\+tcp|coaps\+ws|com-eventbrite-attendee|content|conti|crid|cvs|dab|data|dav|diaspora|dict|did|dis|dlna-playcontainer|dlna-playsingle|dns|dntp|dpp|drm|drop|dtn|dvb|ed2k|elsi|example|facetime|fax|feed|feedready|file|filesystem|finger|first-run-pen-experience|fish|fm|ftp|fuchsia-pkg|geo|gg|git|gizmoproject|go|gopher|graph|gtalk|h323|ham|hcap|hcp|http|https|hxxp|hxxps|hydrazone|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris\.beep|iris\.lwz|iris\.xpc|iris\.xpcs|isostore|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|leaptofrogans|lorawan|lvlt|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|mongodb|moz|ms-access|ms-browser-extension|ms-calculator|ms-drive-to|ms-enrollment|ms-excel|ms-eyecontrolspeech|ms-gamebarservices|ms-gamingoverlay|ms-getoffice|ms-help|ms-infopath|ms-inputapp|ms-lockscreencomponent-config|ms-media-stream-id|ms-mixedrealitycapture|ms-mobileplans|ms-officeapp|ms-people|ms-project|ms-powerpoint|ms-publisher|ms-restoretabcompanion|ms-screenclip|ms-screensketch|ms-search|ms-search-repair|ms-secondary-screen-controller|ms-secondary-screen-setup|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-connectabledevices|ms-settings-displays-topology|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|ms-spd|ms-sttoverlay|ms-transit-to|ms-useractivityset|ms-virtualtouchpad|ms-visio|ms-walk-to|ms-whiteboard|ms-whiteboard-cmd|ms-word|msnim|msrp|msrps|mss|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|ocf|oid|onenote|onenote-cmd|opaquelocktoken|openpgp4fpr|pack|palm|paparazzi|payto|pkcs11|platform|pop|pres|prospero|proxy|pwid|psyc|pttp|qb|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|s3|secondlife|service|session|sftp|sgn|shttp|sieve|simpleledger|sip|sips|skype|smb|sms|smtp|snews|snmp|soap\.beep|soap\.beeps|soldat|spiffe|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|tg|things|thismessage|tip|tn3270|tool|ts3server|turn|turns|tv|udp|unreal|urn|ut2004|v-event|vemmi|ventrilo|videotex|vnc|view-source|wais|webcal|wpid|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc\.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s'
            : implode('|', $protocols);

        /*
         * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (5.0.7).
         *
         * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
         */
        $pattern = '~^
            (LARAVEL_PROTOCOLS)://                                 # protocol
            (((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+)@)?  # basic auth
            (
                ([\pL\pN\pS\-\_\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                                 # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                    |                                                 # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
        $~ixu';

        return preg_match(str_replace('LARAVEL_PROTOCOLS', $protocolList, $pattern), $value) > 0;
    }

    /**
     * Determine if a given value is a valid UUID.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function isUuid($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}$/D', $value) > 0;
    }

    /**
     * Convert a string to kebab case.
     * @param  string  $value
     * @return string
     */
    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

    /**
     * Return the length of the given string.
     * @param  string  $value
     * @param  string|null  $encoding
     * @param  bool  $emojible
     * @return int
     */
    public static function len($value, $encoding = null, $emojible = true)
    {
        if ($emojible) {
            $value = preg_replace('/\p{M}/u', '', $value);
        }
        return mb_strlen($value, $encoding);
        //$length = 0;
        //$valuelength = \strlen($value);
        //for ($x = 0; $x < $valuelength; $x++) {
        //    $char = \ord($value[$x]);
        //    if (($char & 0xc0) != 0x80) {
        //        $length += 1 + ($char >= 0xf0 ? 1 : 0);
        //    }
        //}
        //return $length;
    }

    /**
     * Make a string's first character lowercase.
     * @param  string  $string
     * @return string
     */
    public static function lcfirst($string)
    {
        return static::lower(static::sub($string, 0, 1)).static::sub($string, 1);
    }

    /**
     * Shorts a string if it is too long
     * @param  string  $string  The text to be shorted.
     * @param  int  $limit  The limit or max lenfth of text.
     * @param  string  $extra  Optional. The extra string after shorted value. Default emoty.
     * @return string
     */
    public static function limit($string, $limit, $extra = '...')
    {
        if (self::len($string) > $limit) {
            return self::sub($string, 0, $limit).$extra;
        } else {
            return $string;
        }
    }

    /**
     * Limit the number of words in a string.
     * @param  string  $value
     * @param  int  $words
     * @param  string  $end
     * @return string
     */
    public static function limitWords($value, $words = 100, $end = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (!isset($matches[0]) || static::len($value) === static::len($matches[0])) {
            return $value;
        }

        return static::trim($matches[0]).$end;
    }

    /**
     * @param  string  $string
     * @return string[]
     */
    public static function lines($string)
    {
        return array_map(function ($value) {
            return trim($value);
        }, static::explode("\n", $string));
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * @param $string
     * @param $char
     * @return int
     */
    public static function longestSequence($string, $char = ' ')
    {
        $text_length = static::len($string);
        $maximum = 0;
        $count = 0;
        for ($i = 0; $i < $text_length; $i++) {
            if (static::sub($string, $i, 1) == $char) {
                $count++;
                if ($count > $maximum) {
                    $maximum = $count;
                }
            } else {
                $count = 0;
            }
        }
        return $maximum;
    }

    /**
     * Masks a portion of a string with a repeated character.
     *
     * @param  string  $string
     * @param  string  $character
     * @param  int  $index
     * @param  int|null  $length
     * @param  string  $encoding
     * @return string
     */
    public static function mask($string, $character, $index, $length = null, $encoding = 'UTF-8')
    {
        if ($character === '') {
            return $string;
        }

        $segment = mb_substr($string, $index, $length, $encoding);

        if ($segment === '') {
            return $string;
        }

        $strlen = mb_strlen($string, $encoding);
        $startIndex = $index;

        if ($index < 0) {
            $startIndex = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = mb_substr($string, 0, $startIndex, $encoding);
        $segmentLen = mb_strlen($segment, $encoding);
        $end = mb_substr($string, $startIndex + $segmentLen);

        return $start.str_repeat(mb_substr($character, 0, 1, $encoding), $segmentLen).$end;
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * @param  string  $subject
     * @return string
     */
    public static function match($pattern, $subject)
    {
        preg_match($pattern, $subject, $matches);

        if (!$matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|iterable<string>  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function isMatch($pattern, $value)
    {
        $value = (string) $value;

        if (!is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $_pattern) {
            $_pattern = (string) $_pattern;
            if (preg_match($_pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replaces Arabic and Persian numerals with English numerals.
     *
     * @param  string  $string
     * @return string
     */
    public static function normalizeNumbers($string)
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٤', '٥'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '4', '5'],
            $string,
        );
    }

    /**
     * Replaces English and Persian numerals with Arabic numerals.
     *
     * @param  string  $string
     * @return array|string|string[]
     */
    public static function normalizeArabicNumbers($string)
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '4', '5', '۴', '۵'],
            ['۰', '۱', '۲', '۳', '٤', '٥', '۶', '۷', '۸', '۹', '٤', '٥'],
            $string,
        );
    }

    /**
     * Replaces English and Arabic numerals with Persian numerals.
     *
     * @param  string  $string
     * @return string
     */
    public static function normalizePersianNumbers($string)
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '4', '5', '٤', '٥'],
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '۴', '۵'],
            $string,
        );
    }

    /**
     * Replaces some pseudo-Persian characters with the correct original characters.
     *
     * @param  string  $text
     * @param  bool  $all
     * @return string
     */
    public static function normalizePersianChars($text, $all = true)
    {
        if ($all) {
            $search = [
                'ك', 'ي', 'ی', 'ى', 'ئ', 'ﻯ', 'ة', 'أ', 'إ', 'ؤ', 'ئ', 'بِ', 'پِ', 'تِ', 'ثِ', 'جِ', 'چِ', 'حِ', 'خِ',
                'دِ', 'ذِ',
                'رِ', 'زِ', 'سِ', 'شِ', 'صِ', 'ضِ', 'طِ', 'ظِ', 'عِ', 'غِ', 'فِ', 'قِ', 'کِ', 'گِ', 'لِ',
                'مِ', 'نِ', 'وِ', 'هِ', 'یِ', 'ةِ', 'ءِ', 'آ', 'ى', 'ۀ', 'ؤ',
            ];
            $replace = [
                'ک', 'ی', 'ی', 'ی', 'ی', 'ی', 'ه', 'ا', 'ا', 'و', 'ی', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ',
                'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل',
                'م', 'ن', 'و', 'ه', 'ی', 'ه', 'ء', 'ا', 'ی', 'ه', 'و',
            ];
        } else {
            $search = ['ك', 'ي', 'ی', 'ى', 'ئ', 'ﻯ'];
            $replace = ['ک', 'ی', 'ی', 'ی', 'ی', 'ی'];
        }
        return str_replace($search, $replace, $text);
    }

    /**
     * Converts all consecutive repeated spaces into a single space.
     * @param  string  $string
     * @return string
     */
    public static function normalizeWhitespace($string)
    {
        return preg_replace('/\s+/', ' ', trim($string));
    }

    /**
     * Remove all non-numeric characters from a string.
     * @param  string  $value
     * @return string
     */
    public static function numbers($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Pad both sides of a string with another.
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padBoth($value, $length, $pad = ' ')
    {
        if (function_exists('mb_str_pad')) {
            return mb_str_pad($value, $length, $pad, STR_PAD_BOTH);
        }

        $short = max(0, $length - mb_strlen($value));
        $shortLeft = floor($short / 2);
        $shortRight = ceil($short / 2);

        return static::sub(str_repeat($pad, $shortLeft), 0, $shortLeft).
            $value.
            mb_substr(str_repeat($pad, $shortRight), 0, $shortRight);
    }

    /**
     * Pad the left side of a string with another.
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padLeft($value, $length, $pad = ' ')
    {
        if (function_exists('mb_str_pad')) {
            return mb_str_pad($value, $length, $pad, STR_PAD_LEFT);
        }

        $short = max(0, $length - mb_strlen($value));

        return static::sub(str_repeat($pad, $short), 0, $short).$value;
    }

    /**
     * Pads the right side of a string with another.
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public static function padRight($value, $length, $pad = ' ')
    {
        if (function_exists('mb_str_pad')) {
            return mb_str_pad($value, $length, $pad, STR_PAD_RIGHT);
        }

        $short = max(0, $length - mb_strlen($value));

        return $value.mb_substr(str_repeat($pad, $short), 0, $short);
    }

    /**
     * Parse a Class[@]method style callback into class and method.
     *
     * @param  string  $callback
     * @param  string|null  $default
     * @return array<int, string|null>
     */
    public static function parseCallback($callback, $default = null)
    {
        if (static::contains($callback, "@anonymous\0")) {
            if (mb_substr_count($callback, '@') > 1) {
                return [
                    static::beforeLast($callback, '@'),
                    static::afterLast($callback, '@'),
                ];
            }

            return [$callback, $default];
        }

        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * An alternative for {@see strpos()}.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @param  int  $offset
     * @return int|false
     */
    public static function pos($haystack, $needle, $offset = 0)
    {
        $haystack16 = mb_convert_encoding($haystack, 'UTF-16');
        $needle16 = mb_convert_encoding($needle, 'UTF-16');
        $position = strpos($haystack16, $needle16, $offset << 1);
        return $position === false ? false : ($position >> 1);
    }

    /**
     * Returns the estimated time required to read the text.
     *
     * @param  string  $content
     * @return array Includes two keys: `minutes` and `seconds`.
     */
    public static function readingTime($content)
    {
        $words = static::wordCount(strip_tags($content));
        return [
            'minutes' => floor($words / 200),
            'seconds' => floor($words % 200 / (200 / 60)),
        ];
    }

    /**
     * Replaces the given value in the given string.
     *
     * @param  string|iterable<string>  $search
     * @param  string|iterable<string>  $replace
     * @param  string|iterable<string>  $subject
     * @param  bool  $caseSensitive
     * @return string|string[]
     */
    public static function replace($search, $replace, $subject, $caseSensitive = true)
    {
        if ($search instanceof \Traversable) {
            $search = iterator_to_array($search);
        }

        if ($replace instanceof \Traversable) {
            $replace = iterator_to_array($replace);
        }

        if ($subject instanceof \Traversable) {
            $subject = iterator_to_array($subject);
        }

        return $caseSensitive
            ? str_replace($search, $replace, $subject)
            : str_ireplace($search, $replace, $subject);

        //$utf16Text = mb_convert_encoding($text, 'UTF-16');
        //$utf16Replacement = mb_convert_encoding($replacement, 'UTF-16');
        //$byteOffset = $offset << 1;
        //$byteLength = $length === null ? null : ($length << 1);
        //$utf16Result = substr_replace($utf16Text, $utf16Replacement, $byteOffset, $byteLength);
        //return mb_convert_encoding($utf16Result, 'UTF-8', 'UTF-16');
    }

    /**
     * Replaces the first occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceFirst($search, $replace, $subject)
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the first occurrence of the given value if it appears at the start of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceStart($search, $replace, $subject)
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        if (static::startsWith($subject, $search)) {
            return static::replaceFirst($subject, $search, $replace);
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceLast($search, $replace, $subject)
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value if it appears at the end of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceEnd($search, $replace, $subject)
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        if (static::endsWith($subject, $search)) {
            return static::replaceLast($subject, $search, $replace);
        }

        return $subject;
    }

    /**
     * Replace the patterns matching the given regular expression.
     *
     * @param  array|string  $pattern
     * @param  \Closure|string[]|string  $replace
     * @param  array|string  $subject
     * @param  int  $limit
     * @return string|string[]|null
     */
    public static function replaceMatches($pattern, $replace, $subject, $limit = -1)
    {
        if ($replace instanceof \Closure) {
            return preg_replace_callback($pattern, $replace, $subject, $limit);
        }

        return preg_replace($pattern, $replace, $subject, $limit);
    }

    /**
     * Replaces the first letter of words in a string.
     * @param  string  $needle  The character to be replaced.
     * @param  string  $replace  The character to replace with.
     * @param  string  $subject  The input string.
     * @return string The modified string with the first letter of words replaced.
     */
    public static function replaceFirstLetters($needle, $replace, $subject)
    {
        $words = explode(' ', $subject);
        $subject = '';
        $word_number = 0;
        $words_count = count($words);

        foreach ($words as $word) {
            $word_number++;
            $word_length = mb_strlen($word);
            if ($word_length == 0) {
                $subject .= $word_number < $words_count ? ' ' : '';
                continue;
            }
            $word_first_char = mb_substr($word, 0, 1);
            if ($word_first_char == $needle) {
                $word_chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
                $word_chars[0] = $replace;
                $word = implode('', $word_chars);
            }
            $subject .= $word.($word_number < $words_count ? ' ' : '');
        }

        return $subject;
    }

    /**
     * @param  string|iterable<string>  $needle
     * @param  string|iterable<string>  $replace
     * @param  string|iterable<string>  $subject
     * @return string
     */
    public static function replaceOnce($needle, $replace, $subject)
    {
        return preg_replace('/'.preg_quote($needle, '/').'/', $replace, $subject, 1);
        //$pos = strpos($subject, $search);
        //if ($pos !== false) {
        //    return substr_replace($subject, $replace, $pos, strlen($search));
        //}
        //return $subject;
    }

    /**
     * Remove any occurrence of the given string in the subject.
     *
     * @param  string|iterable<string>  $search
     * @param  string|iterable<string>  $subject
     * @param  bool  $ignoreCase
     * @return string
     */
    public static function remove($search, $subject, $ignoreCase = false)
    {
        return $ignoreCase
            ? str_ireplace($search, '', $subject)
            : str_replace($search, '', $subject);
    }

    /**
     * Reverse the given string.
     *
     * @param  string  $value
     * @return string
     */
    public static function reverse($value)
    {
        return implode(array_reverse(mb_str_split($value)));
    }

    /**
     * @param  string  $length
     * @param  string|array  $source
     * @return string
     */
    public static function random($length, $source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $chars = '';
        if (is_array($source)) {
            $source = array_merge([
                'upper_alphabets' => true,
                'lower_alphabets' => true,
                'numbers' => true,
                'customs' => '',
            ], $source);
            $chars .= $source['upper_alphabets'] ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : false;
            $chars .= $source['upper_alphabets'] ? 'abcdefghijklmnopqrstuvwxyz' : false;
            $chars .= $source['numbers'] ? '0123456789' : false;
            $chars .= $source['customs'] && is_string($source['customs']) ? $source['customs'] : '';
        } elseif (is_string($source)) {
            $chars = $source;
        }
        if (!$chars) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }
        $length = (int) $length;
        $output = "";
        $max = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            try {
                $output .= $chars[random_int(0, $max - 1)];
            } catch (Throwable $e) {
            }
        }
        return $output;
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $prefix
     * @return string
     */
    public static function start($value, $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @param  string  $encoding
     * @return string
     */
    public static function sub($string, $start, $length = null, $encoding = 'UTF-8')
    {
        preg_match_all('/./u', $string, $matches);
        $chars = $matches[0];
        $filteredChars = [];
        foreach ($chars as $char) {
            if (preg_match('/\p{M}/u', $char) && !empty($filteredChars)) {
                $filteredChars[count($filteredChars) - 1] .= $char;
            } else {
                $filteredChars[] = $char;
            }
        }
        if (is_null($length)) {
            $length = count($filteredChars) - $start;
        }
        return implode('', array_slice($filteredChars, $start, $length));

        //return mb_substr($string, $start, $length, $encoding);
        //return mb_convert_encoding(
        //    substr(
        //        mb_convert_encoding($string, 'UTF-16'),
        //        $start << 1,
        //        $length === null ? null : ($length << 1),
        //    ),
        //    'UTF-8',
        //    'UTF-16',
        //);
    }

    /**
     * Telegram UTF-8 multibyte split.
     *
     * @param  string  $string  Text
     * @param  integer  $length  Length
     * @return array<string>
     */
    public static function split($string, $length)
    {
        $result = [];
        foreach (str_split(mb_convert_encoding($string, 'UTF-16'), $length << 1) as $chunk) {
            $chunk = mb_convert_encoding($chunk, 'UTF-8', 'UTF-16');
            if (is_string($chunk)) {
                $result [] = $chunk;
            }
        }
        return $result;
    }

    /**
     * Swap multiple keywords in a string with other keywords.
     *
     * @param  array  $map
     * @param  string  $subject
     * @return string
     */
    public static function swap($map, $subject)
    {
        return strtr($subject, $map);
    }

    /**
     * Remove all "extra" blank space from the given string.
     *
     * @param  string  $value
     * @return string
     */
    public static function squish($value)
    {
        return preg_replace('~(\s|\x{3164}|\x{1160})+~u', ' ', static::trim($value));
    }

    /**
     * Determines if a given string starts with a given substring.
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return 0 === strncmp($haystack, $needle, static::len($needle));
    }

    /**
     * Convert a value to studly caps case.
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn($word) => static::ucfirst($word), $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }

    /**
     * Take the first or last {$limit} characters of a string.
     *
     * @param  string  $string
     * @param  int  $limit
     * @return string
     */
    public static function take($string, $limit)
    {
        if ($limit < 0) {
            return static::sub($string, $limit);
        }

        return static::sub($string, 0, $limit);
    }

    /**
     * Convert the given string to Base64 encoding.
     *
     * @param  string  $string
     * @return string
     */
    public static function toBase64($string)
    {
        return base64_encode($string);
    }

    /**
     * Decode the given Base64 encoded string.
     *
     * @param  string  $string
     * @param  bool  $strict
     * @return string|null
     */
    public static function fromBase64($string, $strict = false)
    {
        $decoded = base64_decode($string, $strict);
        return $decoded === false ? null : $decoded;
    }

    /**
     * Remove all whitespace from both ends of a string.
     *
     * @param  string  $value
     * @param  string|null  $charlist
     * @return string
     */
    public static function trim($value, $charlist = null)
    {
        if ($charlist === null) {
            $trimDefaultCharacters = " \n\r\t\v\0";

            return preg_replace('~^[\s\x{FEFF}\x{200B}\x{200E}'.$trimDefaultCharacters.']+|[\s\x{FEFF}\x{200B}\x{200E}'.$trimDefaultCharacters.']+$~u',
                '', $value) ?? trim($value);
        }

        return trim($value, $charlist);
    }

    /**
     * Remove all whitespace from the beginning of a string.
     *
     * @param  string  $value
     * @param  string|null  $charlist
     * @return string
     */
    public static function ltrim($value, $charlist = null)
    {
        if ($charlist === null) {
            $ltrimDefaultCharacters = " \n\r\t\v\0";

            return preg_replace('~^[\s\x{FEFF}\x{200B}\x{200E}'.$ltrimDefaultCharacters.']+~u', '',
                $value) ?? ltrim($value);
        }

        return ltrim($value, $charlist);
    }

    /**
     * Remove all whitespace from the end of a string.
     *
     * @param  string  $value
     * @param  string|null  $charlist
     * @return string
     */
    public static function rtrim($value, $charlist = null)
    {
        if ($charlist === null) {
            $rtrimDefaultCharacters = " \n\r\t\v\0";

            return preg_replace('~[\s\x{FEFF}\x{200B}\x{200E}'.$rtrimDefaultCharacters.']+$~u', '',
                $value) ?? rtrim($value);
        }

        return rtrim($value, $charlist);
    }


    /**
     * Convert the given string to proper case.
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert the given string to proper case for each word.
     *
     * @param  string  $value
     * @return string
     */
    public static function headline($value)
    {
        $parts = explode(' ', $value);

        $parts = count($parts) > 1
            ? array_map([static::class, 'title'], $parts)
            : array_map([static::class, 'title'], static::ucsplit(implode('_', $parts)));

        $collapsed = static::replace(['-', '_', ' '], '_', implode('_', $parts));

        return implode(' ', array_filter(explode('_', $collapsed)));
    }

    /**
     * Converts the given string to APA-style title case.
     * @param  string  $value
     * @return string
     * @link https://apastyle.apa.org/style-grammar-guidelines/capitalization/title-case
     */
    public static function apa($value)
    {
        if (trim($value) === '') {
            return $value;
        }

        $minorWords = [
            'and', 'as', 'but', 'for', 'if', 'nor', 'or', 'so', 'yet', 'a', 'an',
            'the', 'at', 'by', 'for', 'in', 'of', 'off', 'on', 'per', 'to', 'up', 'via',
            'et', 'ou', 'un', 'une', 'la', 'le', 'les', 'de', 'du', 'des', 'par', 'à',
        ];

        $endPunctuation = ['.', '!', '?', ':', '—', ','];

        $words = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        for ($i = 0; $i < count($words); $i++) {
            $lowercaseWord = mb_strtolower($words[$i]);

            if (static::contains($lowercaseWord, '-')) {
                $hyphenatedWords = explode('-', $lowercaseWord);

                $hyphenatedWords = array_map(function ($part) use ($minorWords) {
                    return (in_array($part, $minorWords) && mb_strlen($part) <= 3)
                        ? $part
                        : static::upper(mb_substr($part, 0, 1)).mb_substr($part, 1);
                }, $hyphenatedWords);

                $words[$i] = implode('-', $hyphenatedWords);
            } else {
                if (in_array($lowercaseWord, $minorWords) &&
                    mb_strlen($lowercaseWord) <= 3 &&
                    !($i === 0 || in_array(mb_substr($words[$i - 1], -1), $endPunctuation))) {
                    $words[$i] = $lowercaseWord;
                } else {
                    $words[$i] = static::upper(mb_substr($lowercaseWord, 0, 1)).mb_substr($lowercaseWord,
                            1);
                }
            }
        }

        return implode(' ', $words);
    }

    /**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::sub($string, 0, 1)).static::sub($string, 1);
    }

    /**
     * Split a string into pieces by uppercase characters.
     *
     * @param  string  $string
     * @return string[]
     */
    public static function ucsplit($string)
    {
        return preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Get the number of words a string contains.
     * @param  string  $string
     * @param  string|null  $characters
     * @return int
     */
    public static function wordCount($string, $characters = null)
    {
        return str_word_count($string, 0, $characters);
        // return count(preg_split('~[^\p{L}\p{N}\']+~u', $text));
    }

    /**
     * Wrap a string to a given number of characters.
     *
     * @param  string  $string
     * @param  int  $characters
     * @param  string  $break
     * @param  bool  $cutLongWords
     * @return string
     */
    public static function wordWrap($string, $characters = 75, $break = "\n", $cutLongWords = false)
    {
        return wordwrap($string, $characters, $break, $cutLongWords);
    }

    /**
     * Wrap the string with the given strings.
     *
     * @param  string  $value
     * @param  string  $before
     * @param  string|null  $after
     * @return string
     */
    public static function wrap($value, $before, $after = null)
    {
        return $before.$value.($after ??= $before);
    }

    /**
     * Unwrap the string with the given strings.
     *
     * @param  string  $value
     * @param  string  $before
     * @param  string|null  $after
     * @return string
     */
    public static function unwrap($value, $before, $after = null)
    {
        if (static::startsWith($value, $before)) {
            $value = static::sub($value, static::len($before));
        }

        if (static::endsWith($value, $after ??= $before)) {
            $value = static::sub($value, 0, -static::len($after));
        }

        return $value;
    }
}