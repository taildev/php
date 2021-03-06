<?php

namespace Tail\Meta;

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
        if (array_key_exists('id', $properties)) {
            $this->setId($properties['id']);
        }

        if (array_key_exists('email', $properties)) {
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
        if (array_key_exists('id', $meta)) {
            $this->id = $meta['id'];
        }

        if (array_key_exists('email', $meta)) {
            $this->email = $meta['email'];
        }

        return $this;
    }

    /**
     * Serialize meta information into an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'email' => $this->email(),
        ];
    }
}
