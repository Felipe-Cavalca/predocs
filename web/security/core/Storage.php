<?php

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
        if ($arquivo == null) {
            $arquivo = "error/index.html";
        }

        $config = new Config();
        parent::__construct("{$config->getPathEnvironment()}/storage/{$arquivo}", $novo);
    }
}
