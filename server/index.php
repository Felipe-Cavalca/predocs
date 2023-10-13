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

    // Crie instâncias dos objetos necessários
    $banco = new Banco();
    $funcoes = new Funcoes();

    // Inicie o banco
    $banco->inicio();

    // Executa a função init()
    $retorno = $funcoes->init() ?? $funcoes->setStatusCode(204);

    // Trate o retorno
    if (is_array($retorno)) {
        echo json_encode($retorno);
    } elseif (is_integer($retorno) || is_string($retorno)) {
        echo json_encode(["retorno" => $retorno]);
    } elseif (is_bool($retorno)) {
        echo json_encode(["status" => $retorno]);
    } else {
        echo $retorno;
    }

    // Verifique a condição e reverta ou salve o banco
    if (isset($_SERVER["HTTP_TEST"])) {
        if (http_response_code() == 200) {
            $funcoes->setStatusCode(202);
        }
        $banco->reverter();
    } else {
        $banco->salvar();
    }
}

// Chame a função para executar o código
executarFramework();
