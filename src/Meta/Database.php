<?php

namespace Tail\Meta;

class Database
{

    /** @var string Name of database */
    protected $name;

    /** @var string Query being run against database */
    protected $query;

    /**
     * Deserialize properties into Database
     */
    public function fillFromArray(array $properties): Database
    {
        if (isset($properties['name'])) {
            $this->setName($properties['name']);
        }

        if (isset($properties['query'])) {
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
            'query' => $this->query(),
        ];
    }
}
