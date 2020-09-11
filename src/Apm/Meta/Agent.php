<?php

namespace Tail\Apm\Meta;

class Agent
{

    /** @var ?string Agent name */
    protected $name;

    /** @var ?string Agent type */
    protected $type;

    /** @var ?string Agent version */
    protected $version;

    /**
     * Default Agent metadata
     */
    public static function createDefault(): Agent
    {
        $agent = new Agent();
        $agent->setName('tail-php');
        $agent->setType('php');

        $version = 'unknown';
        $composerFilepath = __DIR__ . '/../../../../../../composer.lock';
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

        $agent->setVersion($version);

        return $agent;
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
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the agent
     */
    public function setName(?string $name): Agent
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the type of agent
     */
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * Set the type of agent
     */
    public function setType(?string $type): Agent
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the version of the agent
     */
    public function version(): ?string
    {
        return $this->version;
    }

    /**
     * Set the version of the agent
     */
    public function setVersion(?string $version): Agent
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Serialize meta information as an array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'type' => $this->type(),
            'version' => $this->version(),
        ];
    }
}
