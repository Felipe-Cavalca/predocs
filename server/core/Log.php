<?php

/**
 * Classe para manipulação de logs
 */
class Log extends Arquivo
{
    /**
     * Função para escrever algo no storage
     * @version 1
     * @access public
     * @param string $mensagem mensagem que será adicionada no log
     */
    public function __construct(string $mensagem = "")
    {
        if (empty($mensagem))
            return;

        $this->escreveLog($mensagem);

        return;
    }

    /**
     * Função para escrever mensagem de erro no log
     * @version 1
     * @access public
     * @param string $mensagem mensagem de erro
     * @param string $controller nome do controller
     * @param string $function funcao que acusou erro
     */
    public function erroLog(string $msg, string $controller = "", string $function = ""): void
    {
        if (empty($controller))
            $controller = $_GET["controller"];

        if (empty($function))
            $function = $_GET["function"];

        $this->escreveLog("Erro em {$controller} - {$function} : $msg");
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
        $config = new Config();
        $dia = date("Y/m/d");
        $hora = date("H:i:s");
        parent::__construct("{$config->getCaminho("log")}/{$dia}.log");
        parent::criar();
        parent::adicionar("{$dia} {$hora} - {$mensagem}\r\n");
    }
}
