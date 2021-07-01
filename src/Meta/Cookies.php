<?php

namespace Tail\Meta;

class Cookies
{

    /** @var array Custom meta information */
    protected $cookies = [];

    public function __construct(array $cookies = [])
    {
        $this->cookies = $_COOKIE;
        $this->merge($cookies);
    }

    /**
     * Set custom cookie value
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function set($key, $value): Cookies
    {
        $this->cookies[$key] = $value;
        return $this;
    }

    /**
     * Get cookie value
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->cookies)) {
            return $this->cookies[$key];
        }

        return null;
    }

    /**
     * Replace all cookies with the provided key=>value array
     *
     * @param array $cookies
     * @return self
     */
    public function replaceAll(array $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * All custom set cookies
     *
     * @return array
     */
    public function all()
    {
        return $this->cookies;
    }

    /**
     * Merge provided meta array. Any keys provided will overwrite existing metadata.
     *
     * @param array $cookies
     * @return self
     */
    public function merge(array $cookies)
    {
        $this->cookies = array_merge($this->cookies, $cookies);
        return $this;
    }

    /**
     * Serialize meta information into an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }
}
