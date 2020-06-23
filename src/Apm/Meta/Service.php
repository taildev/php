<?php

namespace Tail\Apm\Meta;

class Service
{

    /** @var string Name to identify service */
    protected $name;

    /** @var string Environment name service is running in */
    protected $environment;

    public function __construct(string $name, ?string $environment = null)
    {
        $this->name = $name;
        $this->environment = $environment;
    }

    /**
     * Get name of service
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the service
     */
    public function setName(string $name): Service
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the environment
     */
    public function environment(): ?string
    {
        return $this->environment;
    }

    /**
     * Set the name of the environment
     */
    public function setEnvironment(?string $environment): Service
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Serialize meta information into an array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'environment' => $this->environment(),
        ];
    }
}
