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
    private static $redis;

    public function __construct()
    {
        if (empty(self::$redis)) {
            self::conn();
        }
    }

    private static function conn(): void
    {
        self::$redis = new Redis();
        self::$redis->connect('redis', 6379);
    }

    public function set(string $key, mixed $value, int $expire = 0): bool
    {
        if (is_callable($value)) {
            $value = $value();
        }

        return self::$redis->set($key, serialize($value), $expire);
    }

    public function get(string $key, mixed $value = null, int $expire = 0): mixed
    {
        if (!$this->exists($key) && !empty($value)) {
            $this->set($key, $value, $expire);
        }
        return unserialize(self::$redis->get($key));
    }

    public function exists(string $key): bool
    {
        return self::$redis->exists($key);
    }

    public function del(string $key): bool
    {
        return self::$redis->del($key);
    }
}
