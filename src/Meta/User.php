<?php

namespace Tail\Meta;

use stdClass;

class User
{

    /** @var mixed Unique id for user */
    protected $id;

    /** @var string Email for user */
    protected $email;

    /**
     * Deserialize properties into User
     */
    public function fillFromArray(array $properties): User
    {
        if (isset($properties['id'])) {
            $this->setId($properties['id']);
        }

        if (isset($properties['email'])) {
            $this->setEmail($properties['email']);
        }

        return $this;
    }

    /**
     * Get users ID
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Set unique ID for user
     */
    public function setId($id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get users email
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * Set email for user
     */
    public function setEmail(?string $email): User
    {
        $this->email = $email;
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
        if (isset($meta['id'])) {
            $this->id = $meta['id'];
        }

        if (isset($meta['email'])) {
            $this->email = $meta['email'];
        }

        return $this;
    }

    public function serialize()
    {
        $data = [];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        if (isset($this->email)) {
            $data['email'] = $this->email;
        }

        if ($data === []) {
            return new stdClass();
        }

        return $data;
    }
}
