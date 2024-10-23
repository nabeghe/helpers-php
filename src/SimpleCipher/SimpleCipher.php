<?php namespace Nabeghe\SimpleCipher;

/**
 * Simple Cipher.
 *
 * @method static mixed encrypt($data, $conf = []);
 * @method static mixed decrypt($data, $conf = []);
 * @method mixed encrypt($data);
 * @method mixed decrypt($data);
 */
class SimpleCipher
{
    protected array $conf;

    public function __construct($conf = null)
    {
        self::modifyConfig($conf);
        $this->conf = $conf;
    }

    /**
     * Modifies the configuration.
     *
     * @param  array|string|int  $conf  Configuration or encryption secret (password).
     */
    protected static function modifyConfig(&$conf)
    {
        if (is_string($conf) || is_numeric($conf)) {
            $conf = ['secret' => "$conf"];
        }

        if (!is_array($conf)) {
            $conf = [];
        }

        if (!isset($conf['algo'])) {
            $conf['algo'] = 'aes-256-gcm'; // AES-256-CBC
        }

        if (!isset($conf['secret'])) {
            $conf['secret'] = DefaultSecret::get();
        }
    }

    /**
     * Encrypts raw data.
     *
     * @param  mixed  $data  The raw data. It can be a string, array, object, or anything else.
     *                      But everything gets converted to a string.
     * @param  array|string|int  $conf  Configuration or encryption secret (password).
     * @return string
     */
    protected static function _encrypt($data, $conf = [])
    {
        self::modifyConfig($conf);

        $data = @json_encode($data);

        $passphrase = substr(hash('sha256', $conf['secret'], true), 0, 32);
        $iv_len = openssl_cipher_iv_length($conf['algo']);
        $tag_length = 16;
        $iv = openssl_random_pseudo_bytes($iv_len);
        $tag = ""; // will be filled by openssl_encrypt
        $encrypted = openssl_encrypt(
            $data,
            $conf['algo'],
            $passphrase,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            "",
            $tag_length
        );

        return base64_encode($iv.$encrypted.$tag);
    }

    /**
     * Decrypts encrypted data.
     *
     * @param  string  $data  The encryoted data.
     * @param  array|string|int  $conf  Configuration or encryption secret (password).
     *      There is also a key called `default` that returns a value if the output does not match the specified type.
     * @return mixed
     */
    protected static function _decrypt($data, $conf = [])
    {
        self::modifyConfig($conf);

        $data = base64_decode($data);
        $pasphrase = substr(hash('sha256', $conf['secret'], true), 0, 32);
        $iv_len = openssl_cipher_iv_length($conf['algo']);
        $tag_length = 16;
        $iv = substr($data, 0, $iv_len);
        $ciphertext = substr($data, $iv_len, -$tag_length);
        $tag = substr($data, -$tag_length);

        $output = openssl_decrypt(
            $ciphertext,
            $conf['algo'],
            $pasphrase,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($output === false) {
            return $conf['default'] ?? null;
        }

        try {
            return @json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            return $conf['default'] ?? null;
        }
    }

    public function __call($name, $arguments)
    {
        $conf = $this->conf;
        if (isset($arguments[1])) {
            $conf['default'] = $arguments[1];
        }

        $method = "_$name";
        return static::$method($arguments[0], $conf);
    }

    public static function __callStatic($name, $arguments)
    {
        $method = "_$name";
        return static::$method($arguments[0], $arguments[1] ?? null);
    }
}