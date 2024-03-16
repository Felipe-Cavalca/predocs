<?php

namespace Predocs\Controller;

use Predocs\Interface\ControllerInterface;
use Predocs\Include\Controller;
use Predocs\Attributes\Method;

class Index implements ControllerInterface
{
    use Controller;

    #[Method("GET", "POST")]
    public function index()
    {
        return "Index";
    }
}
