<?php

namespace Predocs\Class;

/**
 * Classe de erro
 *
 * Classe responsável por gerenciar os erros do sistema
 *
 * @package Predocs\Class
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 */
class Erro
{

    public function __call(string $metodo, array $parametros)
    {
        return $this->erro404($metodo, $parametros);
    }

    public function erro404(string $pagina = "", array $dados = [])
    {
        http_response_code(404);
        return [
            "erro" => "404",
            "mensagem" => "Página {$pagina} não encontrada",
            "dados" => $dados,
            "get" => $_GET,
            "post" => $_POST
        ];
    }
}
