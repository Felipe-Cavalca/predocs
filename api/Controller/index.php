<?php

namespace Predocs\Controller;

use Predocs\Interface\ControllerInterface;
use Predocs\Include\Controller;
use Predocs\Attributes\Method;
use Predocs\Attributes\RequiredFields;

class Index implements ControllerInterface
{
    use Controller;

    #[Method(["GET", "POST"])]
    #[RequiredFields([
        "email" => FILTER_VALIDATE_EMAIL,
        "numero" => FILTER_VALIDATE_INT,
    ])]
    public function index()
    {
        return "Index";
    }
}
