<?php

/**
 * Classe para manipulação de logs
 */
class Log extends Arquivo
{
    /**
     * Função para escrever algo no storage
     *
     * Para funções dentro de controller é necessario apenas o primeiro parametro
     * @version 2
     * @access public
     * @param string $mensagem mensagem que será adicionada no log
     * @param string $controller arquivo que está executando
     * @param string $function função que está executando
     * @return void
     */
    public function __construct(string $mensagem = "", string $controller = "", string $function = "")
    {
        if (empty($controller))
            $controller = $_GET["controller"];

        if (empty($function))
            $function = $_GET["function"];

        $this->escreveLog("{$controller} - {$function} : {$mensagem}");

        return;
    }

    /**
     * Função para criar e escrever no arquivo de log
     * @version 1
     * @access private
     * @param string $mensagem Mensagem que será escrita no log
     */
    private function escreveLog(string $mensagem)
    {
        $config = new Config;
        $dia = date("Y/m/d");
        $hora = date("H:i:s");
        parent::__construct("{$config->getCaminho("log")}/{$dia}.log");
        parent::criar();
        parent::adicionar("{$dia} {$hora} - {$mensagem}\r\n");
    }
}
