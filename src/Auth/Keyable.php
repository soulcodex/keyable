<?php

namespace Soulcodex\Keyable\Auth;

class Keyable
{
    /**
     * @var mixed
     */
    protected $policies;

    /**
     * @param mixed $policies
     * @return mixed
     */
    public function registerKeyablePolicies($policies)
    {
        return $this->policies = $policies;
    }

    /**
     * @return mixed
     */
    public function getKeyablePolicies()
    {
        return $this->policies;
    }
}
