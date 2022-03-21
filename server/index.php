<?php

header('Content-Type: application/json');

//inclui os arquivos
include 'core/vars.php';
include 'core/funcoes.php';
include 'core/banco.php';

//pega os dados do post
$_POST = json_decode(file_get_contents("php://input"));

//divite o pagina pela / para qu consiga controler o controller e a função
$_GET['_Pagina'] = explode('/', $_GET['_Pagina']);

//verifica se o arquivo existe
if (isset($_GET['_Pagina']) &&  file_exists('controllers/' . $_GET['_Pagina'][0] . 'Controller.php')) {

    //inclui o arquivo
    include 'controllers/' . $_GET['_Pagina'][0] . 'Controller.php';

    //verifica se a função existe
    if (function_exists($_GET['_Pagina'][1])) {
        //caso exista o parametro para ser enviado a função
        if (isset($_GET['_Pagina'][2])) {
            $retornoFuncao = $_GET['_Pagina'][1]($_GET['_Pagina'][2]);
        } else {
            $retornoFuncao = $_GET['_Pagina'][1]();
        }

        //verifica se o retorno é uma função
        if (is_array($retornoFuncao)) {
            $_Retorno['funcao'] = $retornoFuncao;
        } else {
            $_Retorno['funcao']['retorno'] = $retornoFuncao;
        }

        //define a resposta padrão de sucesso
        $_Retorno['servidor'] = [
            'stts' => true,
            'msg' => 'Função executada com sucesso'
        ];
    }
    //caso a função não exista
    else {
        $_Retorno['servidor'] = [
            'stts' => false,
            'funcao' => 'Função não localizada no controller'
        ];
    }
} else {
    $_Retorno['servidor'] = [
        'status' => false,
        'msg' => 'Controller não encontrado'
    ];
}

//retorna os valores
echo json_encode($_Retorno);
