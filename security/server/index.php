<?php

header("Content-Type: application/json");

//divide o valor passado pela url apartir da "/" para que se consiga o controller e a função a ser chamada
$url = explode("/", $_GET["_Pagina"]);

//vefifica se foi passado o controller
if (isset($url[1]) && file_exists("security/server/controllers/" . $url[1] . "Controller.php")) {
	$_GET["controller"] = $url[1]; //atribui o nome do controller

	//inclui o arquivo
	include "security/server/controllers/" . $_GET["controller"] . "Controller.php";

	//verifica se foi passada a função
	if (isset($url[2]) && function_exists($url[2])) {
		$_GET["funcao"] = $url[2];

		//caso exista o parametro para ser enviado a função
		if (isset($url[3])) {
			$_GET["parametro"] = $url[3];
			$retornoFuncao = $_GET["funcao"]($_GET["parametro"]);
		} else {
			$retornoFuncao = $_GET["funcao"]();
		}

		unset($url); //apaga a url apos usar

		//verifica se o retorno é uma função
		if (is_array($retornoFuncao)) {
			$_Retorno["funcao"] = $retornoFuncao;
		} else {
			$_Retorno["funcao"]["retorno"] = $retornoFuncao;
		}

		//define a resposta padrão de sucesso
		$_Retorno["servidor"] = [
			"stts" => true,
			"msg" => "Função executada com sucesso"
		];

		//verifica se foi passado um parametro
		if (isset($url[3])) {
			$_GET["param"] = $url[3];
			unset($url);
		}
	} else {
		$_Retorno["servidor"] = [
			"stts" => false,
			"funcao" => "Função não localizada no controller"
		];
	}
} else {
	$_Retorno["servidor"] = [
		"status" => false,
		"msg" => "Controller não encontrado"
	];
}

//retorna os valores
echo json_encode($_Retorno);
