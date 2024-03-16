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

        foreach ($fields as $key => $type) {
            if (!isset($_POST[$key])) {
                throw new HttpError("badRequest", [
                    "error" =>  "Campo obrigatório",
                    "fieldName" => $key,
                    "type" => $type,
                    "message" => "O campo $key é obrigatório"
                ]);
            }

            if (gettype($_POST[$key]) != $type) {
                throw new HttpError("badRequest", [
                    "error" =>  "Tipo inválido",
                    "fieldName" => $key,
                    "type" => $type,
                    "message" => "O campo $key deve ser um $type"
                ]);
            }
        }
    }
}
