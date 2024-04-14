<?php

namespace Predocs\Core;

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
            $this->validateAction($objController, $this->action);

            $reflectionMethod = new ReflectionMethod($objController, $this->action);
            $attributes = $this->getAttributes($reflectionMethod);
            $return = $this->runBeforeAttributes($attributes);

            if ($return !== null) {
                return $return;
            }

            $return = $this->runAction($objController, $this->action);
            $this->runAfterAttributes($attributes, $return);
            return $return;
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

    private function getAttributes(ReflectionMethod $reflectionMethod): mixed
    {
        $attributesReturn = [];
        $attributes = $reflectionMethod->getAttributes();
        foreach ($attributes as $attribute) {
            $attributesReturn[] = $attribute->newInstance();
        }
        return $attributesReturn;
    }

    private function runBeforeAttributes($attributes): mixed
    {
        foreach ($attributes as $attribute) {
            if (method_exists($attribute, "beforeRun")) {
                $retorno = $attribute->beforeRun();
                if ($retorno !== null) {
                    return $retorno;
                }
            }
        }
        return null;
    }

    private function runAfterAttributes($attributes, $return): void
    {
        foreach ($attributes as $attribute) {
            if (method_exists($attribute, "afterRun")) {
                $attribute->afterRun($return);
            }
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
