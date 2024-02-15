<?php

namespace Predocs\Include;

use Predocs\class\Erro as Err;

/**
 * Trait Controller
 *
 * Funções base para um controller
 *
 * @package Predocs\Shared
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 */
trait Controller
{
    public array $get = [];
    public array $post = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
    }

    public function __call(string $metodo, array $arguments)
    {
        $err = new Err;
        return $err->erro404($metodo, $arguments, $this->get, $this->post);
    }
}
