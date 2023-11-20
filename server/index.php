<?php

class IndexPreDocs
{
    public static function incluirArquivosDoCore()
    {
        include_once("./core/Funcoes.php");
        include_once("./core/Arquivo.php");
        include_once("./core/Config.php");
        include_once("./core/Banco.php");
        include_once("./core/Storage.php");
        include_once("./core/Log.php");
        include_once("./core/FuncoesApp.php");
    }

    public static function executarFramework()
    {
        static::incluirArquivosDoCore();

        // Crie instâncias dos objetos necessários
        $banco = new Banco();

        // Inicie o banco
        $banco->inicio();

        // Executa a função init()
        $retorno = Funcoes::init() ?? Funcoes::setStatusCode(204);

        // Trate o retorno
        if (is_array($retorno)) {
            print json_encode($retorno);
        } elseif (is_int($retorno) || is_string($retorno)) {
            print json_encode(["retorno" => $retorno]);
        } elseif (is_bool($retorno)) {
            print json_encode(["status" => $retorno]);
        } else {
            print $retorno;
        }

        // Verifique a condição e reverta ou salve o banco
        if (isset($_SERVER["HTTP_TEST"])) {
            if (http_response_code() == 200) {
                Funcoes::setStatusCode(202);
            }
            $banco->reverter();
        } else {
            $banco->salvar();
        }
    }
}

IndexPreDocs::executarFramework();
