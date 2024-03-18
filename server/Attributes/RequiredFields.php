<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Class\HttpError;

#[Attribute]
class RequiredFields
{
    public static function validateRequiredFields($fields)
    {
        $fields = $fields[0];

        foreach ($fields as $key => $params) {
            if(is_int($key)) {
                $key = $params;
                $params = FILTER_DEFAULT;
            }
            static::existField($key);
            static::validateType($key, $params);
        }
    }

    private static function existField($field)
    {
        if (!isset($_POST[$field])) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo não encontrado",
                "fieldName" => $field,
            ]);
        }
    }

    private static function validateType($field, $params)
    {
        if (!filter_var($_POST[$field], $params)) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo inválido",
                "fieldName" => $field,
            ]);
        }
    }
}
