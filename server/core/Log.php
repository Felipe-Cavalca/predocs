<?php

/**
 * Classe para manipulação de logs
 */
class Log extends Arquivo
{

    /**
     * Construtor da classe Log.
     *
     * [ATENÇÃO]: Este construtor será removido em versões futuras.
     * Utilize o método estático registrar() para adicionar mensagens no log.
     *
     * @param string $mensagem Mensagem a ser registrada no log.
     * @param string $controller Arquivo em execução.
     * @param string $function Função em execução.
     */
    public function __construct(string $mensagem = "", string $controller = "", string $function = "")
    {
        $this->registrar(mensagem: $mensagem, controller: $controller, function: $function);
    }

    /**
     * Registra uma mensagem no arquivo de log.
     *
     * @param string $mensagem Mensagem a ser registrada no log.
     * @param string $controller Arquivo em execução.
     * @param string $function Função em execução.
     */
    public static function registrar(string $mensagem = "", string $controller = "", string $function = "")
    {
        $controller = $controller ?: $_GET["controller"];
        $function = $function ?: $_GET["function"];

        $dia = date("Y/m/d");
        $hora = date("H:i:s");
        $caminhoLog = Config::getCaminho("log") . "/{$dia}.log";

        $log = new self($caminhoLog);
        $log->criar();
        $log->adicionar("{$dia} {$hora} - {$controller} - {$function} : {$mensagem}\r\n");
    }
}
