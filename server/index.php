<?php

    header('Content-Type: application/json');

    //inclui os arquivos
    include 'core/vars.php';
    include 'core/funcoes.php';
    include 'core/banco.php';

    //verifica se o arquivo existe
    if(isset($_GET['_Pagina']) &&  file_exists('controllers/'.$_GET['_Pagina'].'.php')){
        $_Retorno['status'] = true;
        include 'controllers/'.$_GET['_Pagina'].'.php';
    }else{
        $_Retorno = [
            'status' => false,
            'msg' => 'pagina não encontrada'
        ];
    }

    //define as variaveis
    if(!isset($_Retorno['status'])){
        $_Retorno['status'] = false;
    }
    if(!isset($_Retorno['msg'])){
        $_Retorno['msg'] = "Sem mensagem";
    }
    if(!isset($_Retorno['erroInterno'])){
        $_Retorno['erroInterno'] = false;
    }

    //retorna os valores
    echo json_encode($_Retorno);
?>