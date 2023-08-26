<?php

function incluirArquivosDoCore()
{
    include_once("./core/Funcoes.php");
    include_once("./core/Arquivo.php");
    include_once("./core/Config.php");
    include_once("./core/Banco.php");
    include_once("./core/Storage.php");
    include_once("./core/Log.php");
    include_once("./core/FuncoesApp.php");
}

function executarFramework()
{
    incluirArquivosDoCore();

    if (isset($_SERVER["HTTP_TEST"])) {
        $banco = new Banco();
        $banco->inicio();
    }

    $funcoes = new Funcoes();
    $retorno = $funcoes->init() ?? "Não foi possível recuperar a saída da função";

    if (is_array($retorno)) {
        echo json_encode($retorno);
    } elseif (is_integer($retorno) || is_string($retorno)) {
        echo json_encode(["retorno" => $retorno]);
    } elseif (is_bool($retorno)) {
        echo json_encode(["status" => $retorno]);
    } else {
        echo $retorno;
    }

    $banco = new Banco();
    if (isset($_SERVER["HTTP_TEST"])) {
        if(http_response_code() == 200){
            $funcoes->setStatusCode(202);
        }
        $banco->reverter();
    }else{
        $banco->salvar();
    }
}

// Chame a função para executar o código
executarFramework();
