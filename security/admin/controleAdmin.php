<?php

/**
 * Faz a tratativa das urls do admin
 *
 * @param string $url - A url que foi solicitada
 * @return void - não retorna dados
 */
function urlAdmin(string $url)
{
	$url = explode("/", $url);

	if (count($url) == 1) {
		header('Location: ./lis/'); //redireciona o usuario para a url correta
		return;
	}

	if ($url[1] == "login" && count($url) == 2) {
		retornar("security/admin/controllers/deslogadoController.php");

		switch (validarPostLogin()) {
			case "logou":
				session_start();
				$_SESSION["logado"] = true;
				echo json_encode(["stts" => true, "msg" => "Login Aceito"]);
				break;
			case "invalido":
				echo json_encode(["stts" => false, "msg" => "Login ou senha invalidos"]);
				break;
			case "view":
				retornar("security/admin/pages/login.html");
				break;
		}
		return;
	}

	if (!validaLogin()) {
		if (count($url) == 2) {
			header("Location: login");
		} else {
			header("Location: ../");
		}
		return;
	}

	if (empty($url[1])) {
		retornar("security/admin/pages/index.html");
		return;
	}

	switch ($url[1]) {
		case "server":
			echo json_encode(validaControllerAdmin($url));
			break;
		default:
			unset($url[0]);
			$url = implode("/", $url);
			retornar($url);
			break;
	}
}

/**
 * @param array $url - array de indices da url
 * @return array - array com a resposta do controller
 */
function validaControllerAdmin(array $url)
{
	$controller = "security/admin/controllers/" . $url[2] . "Controller.php";

	if (empty($url) || !file_exists($controller)) {
		return [
			"servidor" => [
				"status" => false,
				"msg" => "Arquivo não localizado"
			]
		];
	}

	include_once $controller;

	if (function_exists($url[3])) {
		return [
			"servidor" => [
				"status" => true,
				"msg" => "Função executada com sucesso"
			],
			"funcao" => $url[3]()
		];
	}

	return [
		"servidor" => [
			"status" => false,
			"msg" => "função não localizada"
		]
	];
}

/**
 * Função para validar se o admin está logado ou não
 *
 * @return boolean - se está logado ou nn
 */
function validaLogin()
{
	session_start();

	if (isset($_SESSION["logado"]) && $_SESSION["logado"]) {
		return true;
	}

	return false;
}
