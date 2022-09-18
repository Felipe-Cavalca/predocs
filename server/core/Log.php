<?php

/**
 * Classe para manipulação de logs
 */
class Log extends Arquivo
{
    /**
     * Função construtora do arquivo no storage
     *
     * @param string - Mensagem
     */
    public function __construct(string $mensagem)
    {
        $config = new Config();
        $dia = date("Y/m/d");
        $hora = date("H:i:s");
        parent::__construct("{$config->getCaminho("log")}/{$dia}.log");
        $this->criar();
        $this->adicionar("{$dia} {$hora} - {$mensagem}\r\n");
    }
}
