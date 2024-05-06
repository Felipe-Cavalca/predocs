<?php

namespace Predocs\Class;

/**
 * Classe de erro
 *
 * Classe responsável por gerenciar os erros do sistema
 *
 * @package Predocs\Class
 * @author Felipe dos S. Cavalca
 * @version 2.0.0
 * @since 1.1.0
 */
class HttpError extends \Error
{
    private int $statusCode;
    private array|string $return;

    public function __construct(string $error, array $params = [])
    {
        $this->$error($params);
        $this->code = $this->statusCode;
        $this->message = $this->getMessage();

        parent::__construct($this->message, $this->code);
    }

    public function __call($name, $arguments)
    {
        $this->e500();
    }

    public function getReturn(): array|string
    {
        return $this->return;
    }

    public function e500()
    {
        $this->statusCode = 500;
        $this->return = "Erro interno do servidor";
    }

    public function e400($params)
    {
        $this->statusCode = 400;
        $this->return = [
            "status" => false,
            "statusCode" => 400,
            "message" => "Requisição inválida",
            "errors" => $params
        ];
    }

    public function e401($message)
    {
        if (count($message) === 1) {
            $message = $message[0];
        }
        $this->statusCode = 401;
        $this->return = [
            "status" => false,
            "statusCode" => 401,
            "message" => "Não autorizado",
            "errors" => $message
        ];
    }

    public function e404()
    {
        $this->statusCode = 404;
        $this->return = [
            "status" => false,
            "statusCode" => 404,
            "message" => "Página não encontrada"
        ];
    }

    public function e405()
    {
        $this->statusCode = 405;
        $this->return = [
            "status" => false,
            "statusCode" => 405,
            "message" => "Método não suportado"
        ];
    }

    public function e409($message)
    {
        $this->statusCode = 409;
        $this->return = [
            "status" => false,
            "statusCode" => 409,
            "message" => $message ?? "Conflito"
        ];
    }

    public function methodNotAllowed()
    {
        $this->e405();
    }

    public function badRequest($params)
    {
        $this->e400($params);
    }

    public function Unauthorized($message)
    {
        $this->e401($message);
    }

    public function conflict($message)
    {
        $this->e409($message);
    }
}
