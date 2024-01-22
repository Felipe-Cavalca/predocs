<?php

/**
 * Enumeração que define os caminhos utilizados no projeto Predocs.
 */
enum CaminhosPredocs: string
{
    case RAIZ = __DIR__ . "/";
    case CORE = "Core/";

    public function  __get($path): string
    {
        return $this->$path;
    }
}

/**
 * Função responsável por importar classes do core.
 *
 * @param string $nome_classe O nome da classe a ser importada.
 * @return bool Retorna true se a classe foi importada com sucesso, caso contrário retorna false.
 */
spl_autoload_register(function (string $nome_classe) {
    $file = CaminhosPredocs::RAIZ->value .
            CaminhosPredocs::CORE->value .
            $nome_classe . '.php';
    if (!file_exists($file)) {
        return false;
    }
    include_once $file;
    return true;
});

/**
 * Este arquivo é o ponto de entrada para o servidor.
 */
print new Predocs();