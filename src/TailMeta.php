<?php

namespace Tail;

use Tail\Meta\Agent;
use Tail\Meta\Service;
use Tail\Meta\System;
use Tail\Meta\Tags;
use Tail\Meta\User;

class TailMeta
{

    /** @var Agent Agent metadata */
    protected $agent;

    /** @var Service Service metadata */
    protected $service;

    /** @var System System metadata */
    protected $system;

    /** @var Tags Custom metadata */
    protected $tags;

    /** @var User User metadata */
    protected $user;

    public function __construct()
    {
        $this->agent = new Agent();
        $this->service = new Service();
        $this->system = new System();
        $this->tags = new Tags();
        $this->user = new User();
    }

    /**
     * Get/set agent metadata
     *
     * @return Agent
     */
    public function agent(): Agent
    {
        return $this->agent;
    }

    /**
     * Get/set service metadata
     *
     * @return Service
     */
    public function service(): Service
    {
        return $this->service;
    }

    /**
     * Get/set system metadata
     *
     * @return System
     */
    public function system(): System
    {
        return $this->system;
    }

    /**
     * Get/set custom metadata
     *
     * @return Tags
     */
    public function tags(): Tags
    {
        return $this->tags;
    }

    /**
     * Get/set user metadata
     *
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    public function toArray()
    {
        return [
            'agent' => $this->agent->toArray(),
            'service' => $this->service->toArray(),
            'system' => $this->system->toArray(),
            'tags' => $this->tags->toArray(),
            'user' => $this->user->toArray(),
        ];
    }
}
