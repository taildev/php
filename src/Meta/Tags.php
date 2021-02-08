<?php

namespace Tail\Meta;

class Tags
{

    /** @var array Custom meta information */
    protected $tags = [];

    public function __construct(array $tags = [])
    {
        $this->tags = $tags;
    }

    /**
     * Set custom tag value
     */
    public function set(string $key, $value): Tags
    {
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * Get tag value
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->tags)) {
            return $this->tags[$key];
        }

        return null;
    }

    /**
     * Replace all tags with the provided key=>value array
     */
    public function replaceAll(array $tags): Tags
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * All custom set tags
     */
    public function all(): array
    {
        return $this->tags;
    }

    /**
     * Serialize meta information into an array
     */
    public function toArray(): array
    {
        return $this->all();
    }
}
