<?php

namespace Predocs\Core;

use Predocs\Enum\Path;

/**
 * Classe Predocs
 *
 * Esta é a classe principal do sistema Predocs.
 * Ela é responsável por inicializar a configuração e gerenciar o ciclo de vida do sistema.
 *
 * @package Predocs\Core
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 */
final class Predocs
{
    private string $controller = "index";
    private string $action = "index";

    public function __construct()
    {
        Settings::init();
        $this->sanitizeGet();
        $this->sanitizePost();
    }

    public function __toString(): string
    {
        return $this->handleResponse($this->run());
    }

    private function sanitizeGet(): void
    {
        $get = $_GET;
        $url = explode("/", $get["_PagePredocs"]);

        $this->controller = count($url) == 2 ? $url[0] : $this->controller;
        $this->action = count($url) == 2 ? $url[1] : $url[0];

        unset($get["_PagePredocs"]);
        $_GET = $get;
    }

    private function sanitizePost(): void
    {
        $post = $_POST;
        $json = json_decode(file_get_contents('php://input'), true);
        $_POST = (is_array($json) ? $json : $post);
    }

    private function run(): mixed
    {
        $objController = $this->loadController($this->controller);
        return $this->runAction($objController, $this->action);
    }

    private function loadController(string $controller): object
    {
        $controller = Path::CONTROLLERS->value . $controller;
        if (class_exists($controller)) {
            return new $controller();
        } else {
            // Caso não localize retorna a classe de erros
            $controller = Path::CLASSE->value . "Erro";
            return new $controller();
        }
    }

    private function runAction(object $controller, string $action): mixed
    {
        return call_user_func([$controller, $action]);
    }

    private function handleResponse(mixed $retorno): string
    {
        if (is_array($retorno)) {
            return json_encode($retorno);
        } else {
            return (string) $retorno;
        }
    }
}
