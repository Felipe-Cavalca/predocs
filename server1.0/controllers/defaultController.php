<?php

class DefaultController {

    public static function index(): string
    {
        return "Hello World!";
    }

    public static function test(): string
    {
        return "Teste";
    }

    public static function status404(): string
    {
        return "Teste 2";
    }

    public static function status500(): string
    {
        return "Teste 2";
    }
}
