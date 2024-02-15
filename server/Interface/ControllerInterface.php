<?php

namespace Predocs\Interface;

/**
 * Interface ControllerInterface
 *
 * Interface para os controllers
 *
 * @package Predocs\Rules
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 */
interface ControllerInterface
{
    public function __construct();

    public function __call(string $metodo, array $arguments);
}
