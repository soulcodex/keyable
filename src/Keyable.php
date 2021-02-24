<?php

namespace Soulcodex\Keyable;

use Illuminate\Database\Eloquent\Model;
use Soulcodex\Keyable\Models\ApiKey;

/**
 * Trait Keyable
 * @package Soulcodex\Keyable
 * @mixin Model
 */
trait Keyable
{
    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'keyable');
    }

    public function createApiKey()
    {
        return $this->apiKeys()->save(new ApiKey([
            'key' => ApiKey::generate(),
        ]));
    }
}
