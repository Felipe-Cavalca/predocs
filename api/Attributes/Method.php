<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Interface\AttributesInterface;
use Predocs\Class\HttpError;

#[Attribute]
class Method implements AttributesInterface
{

    public function __construct(private mixed $methods)
    {
        if (is_array($this->methods)) {
            $this->validateMethods($this->methods);
        } else {
            $this->validateMethod($this->methods);
        }
    }

    private function validateMethods(array $methods)
    {
        if (!in_array($_SERVER["REQUEST_METHOD"], $methods)) {
            throw new HttpError("methodNotAllowed");
        }
    }

    private function validateMethod(string $method)
    {
        if ($_SERVER["REQUEST_METHOD"] != $method) {
            throw new HttpError("methodNotAllowed");
        }
    }
}
