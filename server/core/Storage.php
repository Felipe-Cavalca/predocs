<?php

/**
 * Classe para manipulação de arquivos no storage
 * @param string $arquivo - caminho para o arquivo
 * @param bool $novo - indica se é um novo arquivo
 */
class Storage extends Arquivo
{
    /**
     * Função construtora do arquivo no storage
     *
     * @param string - caminho até o arquivo dentro do storage
     * @param bool - caso o parametro seja true, um novo arquivo será criado
     */
    public function __construct(string $arquivo, bool $novo = false)
    {
        $config = new Config();
        parent::__construct("{$config->getCaminho("storage")}/{$arquivo}", $novo);
    }
}
