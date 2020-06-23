<?php

namespace Tail\Apm\Meta;

class Database
{

    const READ_OPERATION = 'read';
    const WRITE_OPERATION = 'write';

    /** @var string Name of database */
    protected $name;

    /** @var string Operation being performed, such as read, write, etc. */
    protected $operation;

    /** @var string Query being run against database */
    protected $query;

    /**
     * Deserialize properties into Database
     */
    public function fillFromArray(array $properties): Database
    {
        if (array_key_exists('name', $properties)) {
            $this->setName($properties['name']);
        }

        if (array_key_exists('operation', $properties)) {
            $this->setOperation($properties['operation']);
        }

        if (array_key_exists('query', $properties)) {
            $this->setQuery($properties['query']);
        }

        return $this;
    }

    /**
     * Get the name of the database
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the database
     */
    public function setName(?string $name): Database
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the database operation
     */
    public function operation(): ?string
    {
        return $this->operation;
    }

    /**
     * Set the database operation, such as read, write, etc.
     */
    public function setOperation(?string $operation): Database
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * Mark the operation as "read"
     */
    public function isReadOperation(): Database
    {
        return $this->setOperation(self::READ_OPERATION);
    }

    /**
     * Mark the operation as "write"
     */
    public function isWriteOperation(): Database
    {
        return $this->setOperation(self::WRITE_OPERATION);
    }

    /**
     * Get the database query
     */
    public function query(): ?string
    {
        return $this->query;
    }

    /**
     * Set the database query
     */
    public function setQuery(?string $query): Database
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Serialize meta information as an array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'operation' => $this->operation(),
            'query' => $this->query(),
        ];
    }
}
