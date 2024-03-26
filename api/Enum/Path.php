<?php

namespace Predocs\Enum;

enum Path: string
{
    case CLASSE = "Predocs\\Class\\";
    case CONTROLLERS = "Predocs\\Controller\\";
    case MODEL = "Predocs\\Model\\";
}
