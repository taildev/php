<?php

namespace Tail\Meta;

class System
{

    /** @var string Hostname of system */
    protected $hostname;

    public function __construct()
    {
        $this->hostname = gethostname();
    }

    /**
     * Deserialize properties into System
     */
    public function fillFromArray(array $properties): System
    {
        if (array_key_exists('hostname', $properties)) {
            $this->setHostname($properties['hostname']);
        }

        return $this;
    }

    /**
     * Get the system hostname
     */
    public function hostname()
    {
        return $this->hostname;
    }

    /**
     * Set the system hostname
     */
    public function setHostname($hostname): System
    {
        $this->hostname = $hostname;
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
        if (array_key_exists('hostname', $meta)) {
            $this->hostname = $meta['hostname'];
        }

        return $this;
    }

    /**
     * Serialize meta information into an array
     */
    public function toArray(): array
    {
        return [
            'hostname' => $this->hostname(),
        ];
    }
}
