<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Class\HttpError;
use Predocs\Interface\AttributesInterface;

#[Attribute]
class RequiredFields implements AttributesInterface
{

    public function __construct(private mixed $fields)
    {
        $this->validateRequiredFields($this->fields);
    }

    private function validateRequiredFields(array $fields)
    {
        foreach ($fields as $key => $params) {
            if (is_int($key)) {
                $key = $params;
                $params = FILTER_DEFAULT;
            }
            static::existField($key);
            static::validateType($key, $params);
        }
    }

    private function existField($field)
    {
        if (!isset($_POST[$field])) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo não encontrado",
                "fieldName" => $field,
            ]);
        }
    }

    private function validateType($field, $params)
    {
        if (!filter_var($_POST[$field], $params)) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo inválido",
                "fieldName" => $field,
            ]);
        }
    }
}
