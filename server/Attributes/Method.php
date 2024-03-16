<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Class\HttpError;

#[Attribute]
class Method
{
    public static function validateMethod($methods){
        if (!in_array($_SERVER["REQUEST_METHOD"], $methods)) {
            throw new HttpError("methodNotAllowed");
        }
    }
}
