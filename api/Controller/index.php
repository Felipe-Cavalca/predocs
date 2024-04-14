<?php

namespace Predocs\Controller;

use Predocs\Interface\ControllerInterface;
use Predocs\Include\Controller;
use Predocs\Attributes\Method;
use Predocs\Attributes\RequiredFields;
use Predocs\Attributes\RequiredParams;
use Predocs\Attributes\Cache;

class Index implements ControllerInterface
{
    use Controller;

    #[Method(["GET", "POST"])]
    #[RequiredFields([
        "email" => FILTER_VALIDATE_EMAIL,
        "numero" => FILTER_VALIDATE_INT,
    ])]
    #[RequiredParams(["id"])]
    #[Cache("index-index", 10)]
    public function index()
    {
        $time = date("H:i:s");
        return "Index " . $time;
    }
}
