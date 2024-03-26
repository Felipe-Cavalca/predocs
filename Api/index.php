<?php

namespace Predocs;

use Predocs\Core\Predocs;

/**
 * Função responsável por importar classes do sistema.
 *
 * @param string $className O nome da classe a ser importada.
 * @return bool Retorna true se a classe foi importada com sucesso, caso contrário retorna false.
 */
spl_autoload_register(
    function (string $className): bool {

        $folders = explode("\\", $className);
        $folders = array_slice($folders, 1);
        $folders = implode(DIRECTORY_SEPARATOR, $folders);

        $file = __DIR__ . DIRECTORY_SEPARATOR . $folders . ".php";

        if (!file_exists($file)) {
            return false;
        }

        include_once $file;
        return true;
    }
);

/**
 * Este arquivo é o ponto de entrada para o servidor.
 */
print new Predocs();
