<?php

namespace Predocs\Attributes;

use Attribute;
use Predocs\Class\HttpError;
use Predocs\Interface\AttributesInterface;

#[Attribute]
class RequiredParams implements AttributesInterface
{

    public function __construct(private mixed $params)
    {
        $this->validateRequiredParams($this->params);
    }

    private function validateRequiredParams(array $params)
    {
        foreach ($params as $field => $param) {
            if (is_int($field)) {
                $field = $param;
                $param = FILTER_DEFAULT;
            }
            static::existParam($field);
            static::validateType($field, $param);
        }
    }

    private function existParam($field)
    {
        if (!isset($_GET[$field])) {
            throw new HttpError("badRequest", [
                "error" =>  "Parametro não encontrado",
                "fieldName" => $field,
            ]);
        }
    }

    private function validateType($field, $param)
    {
        if (!filter_var($_GET[$field], $param)) {
            throw new HttpError("badRequest", [
                "error" =>  "Campo inválido",
                "fieldName" => $field,
            ]);
        }
    }
}
