<?php

namespace Predocs\Controller;

use Predocs\Interface\ControllerInterface;
use Predocs\Include\Controller;

class Index implements ControllerInterface
{
    use Controller;

    public function index()
    {
        return "Index";
    }
}
