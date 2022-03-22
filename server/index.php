<?php

header('Content-Type: application/json');

//variavel da pasta onde está os dados - mudar o nome da pasta de tempos em tempos por questões de segurança
$_Pasta = 'bkebguiwrlfnwebfuoiwebofewbo/';

//pega os dados do post
$_POST = json_decode(file_get_contents("php://input"));

//divite o pagina pela / para qu consiga controler o controller e a função
if (isset($_GET['_Pagina'])) {
    $_GET['_Pagina'] = explode('/', $_GET['_Pagina']);

    //verifica se o arquivo existe
    if (isset($_GET['_Pagina']) &&  file_exists($_Pasta . 'controllers/' . $_GET['_Pagina'][0] . 'Controller.php')) {

        //inclui os arquivos
        include $_Pasta . 'core/vars.php';
        include $_Pasta . 'core/funcoes.php';
        include $_Pasta . 'core/banco.php';
        //inclui o arquivo
        include $_Pasta . 'controllers/' . $_GET['_Pagina'][0] . 'Controller.php';

        //verifica se a função existe
        if (isset($_GET['_Pagina'][1]) && function_exists($_GET['_Pagina'][1])) {
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
} else {
    $_Retorno['servidor'] = [
        'status' => false,
        'msg' => 'Nenhum caminho definido'
    ];
}

//retorna os valores
echo json_encode($_Retorno);
