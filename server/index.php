<?php

//inclui os arquivos do core

include_once("./core/Funcoes.php");
include_once("./core/Arquivo.php");
include_once("./core/Config.php");
include_once("./core/Banco.php");
include_once("./core/Storage.php");
include_once("./core/Log.php");
include_once("./core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

//inicia o framework e escreve sua saida
init();

/**
 * Função ao ser chamada sempre que receber uma nova request
 * @return void - porem faz manipução de dados - chama controllers etc
 */
function init()
{
	configPHP(); //seta as config do php
	post(); //organiza o $_POST
	get(); //organiza o $_GET

	if (empty($_GET["controller"])) {
		naoEncontrado();
		return 0;
	}

	$controller = incluiController($_GET["controller"]);

	if ($controller === false) {
		naoEncontrado();
		return 0;
	} else if ($controller === true) {
		if (function_exists($_GET["function"])) {
			$returnFunction = call_user_func($_GET["function"], $_GET["param"]);
		} else {
			naoEncontrado();
			return 0;
		}
	} else if (method_exists($controller, $_GET["function"])) {
		$returnFunction = call_user_func([$controller, $_GET["function"]], $_GET["param"]);
	}

	switch (gettype($returnFunction)) {
		case "array":
			echo json_encode($returnFunction);
			break;
		case "integer":
		case "string":
			echo json_encode(["retorno" => $returnFunction]);
			break;
		case "boolean":
			echo json_encode(["status" => $returnFunction]);
			break;
		default:
			echo $returnFunction;
	}

	return 0;
}
