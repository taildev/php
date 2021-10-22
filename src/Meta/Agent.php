<?php

namespace Tail\Meta;

use stdClass;

class Agent
{

    /** @var ?string Agent name */
    protected $name;

    /** @var ?string Agent type */
    protected $type;

    /** @var ?string Agent version */
    protected $version;

    public function __construct()
    {
        $this->setName('tail-php');
        $this->setType('php');

        $version = 'unknown';
        $composerFilepath = __DIR__ . '/../../../../../composer.lock';
        if (file_exists($composerFilepath)) {
            $composerContent = file_get_contents($composerFilepath);
            $composer = json_decode($composerContent);
            foreach ($composer->packages as $package) {
                if ($package->name === 'taildev/php') {
                    $version = $package->version;
                    break;
                }
            }
        }

        $this->setVersion($version);
    }

    /**
     * Deserialize properties into Agent
     */
    public function fillFromArray(array $properties): Agent
    {
        if (isset($properties['name'])) {
            $this->setName($properties['name']);
        };

        if (isset($properties['type'])) {
            $this->setType($properties['type']);
        };

        if (isset($properties['version'])) {
            $this->setVersion($properties['version']);
        };

        return $this;
    }

    /**
     * Get the name of the agent
     *
     * @return string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the name of the agent
     *
     * @param string|null $name
     * @return Agent
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the type of agent
     *
     * @return string|null
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Set the type of agent
     *
     * @param string|null $type
     * @return Agent
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the version of the agent
     *
     * @return string|null
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * Set the version of the agent
     *
     * @param string|null $version
     * @return Agent
     */
    public function setVersion($version)
    {
        $this->version = $version;
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

        if (isset($meta['type'])) {
            $this->type = $meta['type'];
        }

        if (isset($meta['version'])) {
            $this->version = $meta['version'];
        }

        return $this;
    }

    public function serialize()
    {
        $data = [];

        if (isset($this->name)) {
            $data['name'] = $this->name;
        }
        if (isset($this->type)) {
            $data['type'] = $this->type;
        }
        if (isset($this->version)) {
            $data['version'] = $this->version;
        }

        if ($data === []) {
            return new stdClass();
        }

        return $data;
    }
}
