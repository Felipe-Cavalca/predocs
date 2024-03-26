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
        foreach ($fields as $field => $param) {
            if (is_int($field)) {
                $field = $param;
                $param = FILTER_DEFAULT;
            }
            static::existField($field);
            static::validateType($field, $param);
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

    private function validateType($field, $param)
    {
        if (!filter_var($_POST[$field], $param)) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo inválido",
                "fieldName" => $field,
            ]);
        }
    }
}
