<?php

namespace Soulcodex\Keyable\Facades;

use Illuminate\Support\Facades\Facade;
use Soulcodex\Keyable\Auth\Keyable as KeyableAuth;

/**
 * Class Keyable
 */
class Keyable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return KeyableAuth::class;
    }
}
