<?php

namespace Tail;

use Tail\Meta\Agent;
use Tail\Meta\Service;
use Tail\Meta\System;
use Tail\Meta\Tags;
use Tail\Meta\User;

class TailMeta
{
    /** Agent metadata */
    protected ?Agent $agent = null;

    /** Service metadata */
    protected ?Service $service = null;

    /** System metadata */
    protected ?System $system = null;

    /** Custom metadata */
    protected ?Tags $tags = null;

    /** User metadata */
    protected ?User $user = null;

    /**
     * Get/set agent metadata
     */
    public function agent(): Agent
    {
        if ($this->agent === null) {
            $this->agent = new Agent();
        }
        return $this->agent;
    }

    /**
     * Determine if agent information is set
     */
    public function hasAgent(): bool
    {
        return $this->agent !== null;
    }

    /**
     * Get/set service metadata
     */
    public function service(): Service
    {
        if ($this->service === null) {
            $this->service = new Service();
        }

        return $this->service;
    }

    public function hasService(): bool
    {
        return $this->service !== null;
    }

    /**
     * Get/set system metadata
     */
    public function system(): System
    {
        if ($this->system === null) {
            $this->system = new System();
        }

        return $this->system;
    }

    /**
     * Determine if system information is present
     */
    public function hasSystem(): bool
    {
        return $this->system !== null;
    }

    /**
     * Get/set custom metadata
     */
    public function tags(): Tags
    {
        if ($this->tags === null) {
            $this->tags = new Tags();
        }

        return $this->tags;
    }

    /**
     * Determine if tag information is present
     */
    public function hasTags(): bool
    {
        return $this->tags !== null;
    }

    /**
     * Get/set user metadata
     */
    public function user(): User
    {
        if ($this->user === null) {
            $this->user = new User();
        }

        return $this->user;
    }

    /**
     * Determine if user information is present
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }
}
