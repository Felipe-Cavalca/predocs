<?php

namespace Predocs\Core;

use Redis;

/**
 * Classe Cache
 *
 * Esta classe é responsável por gerenciar o cache.
 *
 * @package Predocs\Core
 * @version 1.0.0
 * @since 1.0.0
 */
class Cache
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
    }

    public function set(string $key, mixed $value, int $expire = 0): bool
    {
        return $this->redis->set($key, serialize($value), $expire);
    }

    public function get(string $key): mixed
    {
        return unserialize($this->redis->get($key));
    }
}
