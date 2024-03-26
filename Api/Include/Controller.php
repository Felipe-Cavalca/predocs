<?php

namespace Predocs\Include;

/**
 * Trait Controller
 *
 * Funções base para um controller
 *
 * @package Predocs\Shared
 * @author Felipe dos S. Cavalca
 * @version 1.1.0
 * @since 1.1.0
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
}
