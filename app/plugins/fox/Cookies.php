<?php

namespace Fox;

/**
 * Class Cookies
 * @package Fox
 */
class Cookies
{
    private static ?Cookies $instance = null;
    private array $cookies;
    private string $path;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(web_root);
        }
        return self::$instance;
    }

    /**
     * Cookies constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path    = $path;
        $this->cookies = $_COOKIE;
    }

    /**
     * Returns a value from $_COOKIE
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        return $this->has($key) ? $this->cookies[$key] : null;
    }

    /**
     * Returns if $_COOKIE contains a key.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->cookies[$key]) && $this->cookies[$key] !== '';
    }

    /**
     * Updates an existing cookie with a value. Returns false if cookie doesn't exist.
     * @param string $key
     * @param string $value
     * @param int|null $expires
     * @return bool
     */
    public function update(string $key, string $value, ?int $expires = null): bool
    {
        if (!$this->has($key)) {
            return false;
        }

        $expires ??= 86400;
        setcookie($key, $value, time() + $expires, $this->path);
        return true;
    }

    /**
     * Sets a cookie with an expire time. 86400 = 1 day.
     * @param string $key
     * @param string $value
     * @param int $expires
     */
    public function set(string $key, string $value, int $expires = 86400): void
    {
        setcookie($key, $value, time() + $expires, $this->path);
    }

    /**
     * Expires a cookie by setting its time in the past.
     * @param string $key
     */
    public function delete(string $key): void
    {
        setcookie($key, '', time() - 1000, $this->path);
    }
}