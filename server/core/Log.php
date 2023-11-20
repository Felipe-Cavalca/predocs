<?php

/**
 * Classe para manipulação de logs
 */
class Log extends Arquivo
{
    /**
     * Construtor da classe Log.
     *
     * @param string $mensagem Mensagem a ser registrada no log.
     * @param string $controller Arquivo em execução.
     * @param string $function Função em execução.
     */
    public function __construct(string $mensagem = "", string $controller = "", string $function = "")
    {
        $controller = $controller ?: $_GET["controller"];
        $function = $function ?: $_GET["function"];

        $this->escreveLog("{$controller} - {$function} : {$mensagem}");
    }

    /**
     * Escreve a mensagem no arquivo de log.
     *
     * @param string $mensagem Mensagem a ser registrada no log.
     */
    private function escreveLog(string $mensagem)
    {
        $config = new Config;
        $dia = date("Y/m/d");
        $hora = date("H:i:s");
        $caminhoLog = "{$config->getCaminho("log")}/{$dia}.log";

        $log = new self($caminhoLog);
        $log->criar();
        $log->adicionar("{$dia} {$hora} - {$mensagem}\r\n");
    }
}
