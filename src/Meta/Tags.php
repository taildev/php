<?php

namespace Tail\Meta;

use stdClass;

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
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function set($key, $value): Tags
    {
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * Get tag value
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key)
    {
        if (isset($this->tags[$key])) {
            return $this->tags[$key];
        }

        return null;
    }

    /**
     * Replace all tags with the provided key=>value array
     *
     * @param array $tags
     * @return self
     */
    public function replaceAll(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * All custom set tags
     *
     * @return array
     */
    public function all()
    {
        return $this->tags;
    }

    /**
     * Merge provided meta array. Any keys provided will overwrite existing metadata.
     *
     * @param array $tags
     * @return self
     */
    public function merge(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }

    public function serialize()
    {
        $data = $this->all();
        if ($data === []) {
            return new stdClass();
        }

        return $data;
    }
}
