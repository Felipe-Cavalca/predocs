<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Interface\AttributesInterface;
use Predocs\Core\Cache as CoreCache;

#[Attribute]
class Cache implements AttributesInterface
{
    private string $key = "";
    private int $time = 0;
    private CoreCache $cache;

    public function __construct($key, $time, $fieldsSession = [])
    {
        $this->key = serialize([
            "key" => $key,
            "POST" => serialize($_POST),
            "GET" => serialize($_GET),
            "SESSION" => $this->getFieldsSession($fieldsSession),
        ]);
        $this->time = $time;
        $this->cache = new CoreCache();
    }

    public function __destruct()
    {
    }

    public function beforeRun(): mixed
    {
        if ($this->cache->exists($this->key)) {
            return $this->cache->get($this->key);
        }
        return null;
    }

    public function afterRun($return): void
    {
        $this->cache->set($this->key, $return, $this->time);
    }

    private function getFieldsSession($fieldsSession): string
    {
        $fields = [];
        foreach ($fieldsSession as $field) {
            $fields[$field] = $_SESSION[$field];
        }
        return serialize($fields);
    }
}
