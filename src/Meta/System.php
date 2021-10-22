<?php

namespace Tail\Meta;

use stdClass;

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
        if (isset($properties['hostname'])) {
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
        if (isset($meta['hostname'])) {
            $this->hostname = $meta['hostname'];
        }

        return $this;
    }

    public function serialize()
    {
        $data = [];

        if (isset($this->hostname)) {
            $data['hostname'] = $this->hostname;
        }

        if ($data === []) {
            return new stdClass();
        }

        return $data;
    }
}
