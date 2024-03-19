<?php

namespace Predocs\Core;

use Predocs\Attributes\Method;
use Predocs\Attributes\RequiredFields;
use Predocs\Enum\Path;
use Predocs\Class\HttpError;
use ReflectionMethod;

/**
 * Classe Predocs
 *
 * Esta é a classe principal do sistema Predocs.
 * Ela é responsável por inicializar a configuração e gerenciar o ciclo de vida do sistema.
 *
 * @package Predocs\Core
 * @author Felipe dos S. Cavalca
 * @version 1.1.0
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
        $url = explode("/", $get["_PagePredocs"] ?? "");

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
        try {
            $this->validateController($this->controller);
            $objController = $this->loadController($this->controller);
            $methodReflection = new ReflectionMethod($this->controller, $this->action);
            $this->validateAction($objController, $this->action);
            $this->validateMethod($methodReflection);
            $this->validateRequiredFields($methodReflection);
            return $this->runAction($objController, $this->action);
        } catch (HttpError $th) {
            http_response_code($th->getCode());
            return $th->getReturn();
        }
    }

    private function validateController(string $controller): void
    {
        $controller = Path::CONTROLLERS->value . $controller;
        if (!class_exists($controller)) {
            throw new HttpError("e404");
        }
    }

    private function loadController(string $controller): object
    {
        $controller = Path::CONTROLLERS->value . $controller;
        return new $controller();
    }

    private function validateAction(object $controller, string $action): void
    {
        if (!method_exists($controller, $action)) {
            throw new HttpError("e404");
        }
    }

    private function validateMethod(ReflectionMethod $methodReflection): void
    {
        $attributeMethod = $methodReflection->getAttributes("Predocs\Attributes\Method");
        if ($attributeMethod) {
            $methods = $attributeMethod[0]->getArguments();
            Method::validateMethod($methods);
        }
    }

    private function validateRequiredFields(ReflectionMethod $methodReflection): void
    {
        $attributeRequiredFields = $methodReflection->getAttributes("Predocs\Attributes\RequiredFields");
        if ($attributeRequiredFields) {
            $requiredFields = $attributeRequiredFields[0]->getArguments();
            RequiredFields::validateRequiredFields($requiredFields);
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
