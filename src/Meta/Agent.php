<?php

namespace Tail\Meta;

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
        if (array_key_exists('name', $properties)) {
            $this->setName($properties['name']);
        };

        if (array_key_exists('type', $properties)) {
            $this->setType($properties['type']);
        };

        if (array_key_exists('version', $properties)) {
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
        if (array_key_exists('name', $meta)) {
            $this->name = $meta['name'];
        }

        if (array_key_exists('type', $meta)) {
            $this->type = $meta['type'];
        }

        if (array_key_exists('version', $meta)) {
            $this->version = $meta['version'];
        }

        return $this;
    }

    /**
     * Serialize meta information as an array
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name(),
            'type' => $this->type(),
            'version' => $this->version(),
        ];
    }
}
