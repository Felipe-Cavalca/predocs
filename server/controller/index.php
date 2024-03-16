<?php

namespace Predocs\Controller;

use Predocs\Interface\ControllerInterface;
use Predocs\Include\Controller;
use Predocs\Attributes\Method;
use Predocs\Attributes\RequiredFields;

class Index implements ControllerInterface
{
    use Controller;

    #[Method("POST", "PUT")]
    #[RequiredFields([
        "email" => "string",
        "numero" => "integer"
    ])]
    public function index()
    {
        return "Index";
    }
}
