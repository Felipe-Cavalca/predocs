<?php

urlAdmin($_GET['_Pagina']);

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
		$arquivo = new Arquivo("security/admin/controllers/deslogadoController.php");
		$arquivo->renderiza();

		switch (validarPostLogin()) {
			case "logou":
				session_start();
				$_SESSION["logadoAdminFramework"] = true;
				echo json_encode(["stts" => true, "msg" => "Login Aceito"]);
				return;
			case "invalido":
				echo json_encode(["stts" => false, "msg" => "Login ou senha invalidos"]);
				return;
			case "view":
				$arquivo->path = "security/admin/pages/login.html";
				$arquivo->renderiza();
				return;
		}
		return;
	}

	if (!validaLoginAdmin()) {
		if (count($url) == 2) {
			header("Location: login");
		} else {
			header("Location: ../");
		}
		return;
	}

	if (empty($url[1])) {
		$arquivo = new Arquivo("security/admin/pages/index.html");
		$arquivo->renderiza();
		return;
	}

	switch ($url[1]) {
		case "server":
			echo json_encode(validaControllerAdmin($url));
			return;
		default:
			unset($url[0]);
			$arquivo = new Arquivo(implode("/", $url));
			$arquivo->renderiza();
			return;
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

	$arquivo = new Arquivo($controller);
	$arquivo->renderiza();

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
function validaLoginAdmin()
{
	session_start();

	if (isset($_SESSION["logadoAdminFramework"]) && $_SESSION["logadoAdminFramework"]) {
		return true;
	}

	return false;
}
