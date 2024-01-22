<?php

class Predocs
{
    // Props

    // Metodos magicos

    public function __construct()
    {
        spl_autoload_register(['Predocs', 'autoloadModel']);
        spl_autoload_register(['Predocs', 'autoloadController']);
        Config::init();
    }

    public function __destruct()
    {
        print "\nDestruindo " . __CLASS__ . "\n";
    }

    public function __toString()
    {
        return __CLASS__;
    }

    // Gets

    // Sets

    // Internas

    // Externas

    final protected static function autoloadController(string $classe): bool
    {
        $caminho = CaminhosPredocs::RAIZ->value .
                   CaminhosPredocs::CONTROLLER->value .
                   $classe . '.php';

        if (file_exists($caminho)) {
            include_once $caminho;
            return true;
        } else {
            return false;
        }
    }

    final protected static function autoloadModel(string $classe): bool
    {
        $caminho = CaminhosPredocs::RAIZ->value .
            CaminhosPredocs::MODEL->value .
            $classe . '.php';

        if (file_exists($caminho)) {
            include_once $caminho;
            return true;
        } else {
            return false;
        }
    }
}
