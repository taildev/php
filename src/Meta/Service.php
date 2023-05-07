<?php

namespace Tail\Meta;

use stdClass;

class Service
{
    /** @var string|null Name to identify service */
    protected $name;

    /** @var string|null Environment name service is running in */
    protected $environment;

    public function __construct(?string $name = null, ?string $environment = null)
    {
        $this->name = $name;
        $this->environment = $environment;
    }

    /**
     * Get name of service
     *
     * @return string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the name of the service
     *
     * @return Service
     */
    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the environment
     *
     * @return string|null
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * Set the name of the environment
     *
     * @return Service
     */
    public function setEnvironment(?string $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Merge provided meta array. Any keys provided will overwrite existing metadata.
     *
     * @param array $meta
     * @return self
     */
    public function merge(array $meta)
    {
        if (isset($meta['name'])) {
            $this->name = $meta['name'];
        }

        if (isset($meta['environment'])) {
            $this->environment = $meta['environment'];
        }

        return $this;
    }

    public function serialize()
    {
        $data = [];

        if (isset($this->name)) {
            $data['name'] = $this->name;
        }
        if (isset($this->environment)) {
            $data['environment'] = $this->environment;
        }

        if ($data === []) {
            return new stdClass();
        }

        return $data;
    }
}
