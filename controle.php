<?php

//incluindo as classes
include_once("security/core/Funcoes.php");
include_once("security/core/Arquivo.php"); //arquivo estende a funcoes
include_once("security/core/Config.php"); //config estende a arquivo
include_once("security/core/Banco.php"); //banco estende a config

//pega os dados do post
$_POST = json_decode(file_get_contents("php://input"));

try {
	if (isset($_GET['_Pagina'])) {
		switch (explode("/", $_GET['_Pagina'])[0]) {
			case "api":
			case "server":
				retornar("security/server/index.php");
				break;
			case "coreJs":
				retornar("security/web/core/index.js");
				break;
			case "coreCss":
				retornar("security/web/core/index.css");
				break;
			case "varsApp":
				getVarsApp();
				break;
			case "framework":
			case "midia":
			case "components":
			case "css":
			case "js":
				retornar("security/web/" . $_GET["_Pagina"]);
				break;
			default:
				retornar("security/web/pages/" . $_GET["_Pagina"]);
				break;
		};
	} else {
		retornar("security/web/pages/index.html");
	}
} catch (Exception $e) {
	retornar("error/internal-server.html");
}

/**
 * Função para retornar o que foi solicitado
 *
 * @param string - caminho do arquivo a ser renderizado
 * @return void
 */
function retornar(string $caminho)
{
	if (!file_exists($caminho)) {
		$arquivoErro = "error/not-found/nao-encontrado." . getExt($caminho);
		if (file_exists($arquivoErro)) {
			$caminho = $arquivoErro;
		} else {
			$caminho = "error/not-found/nao-encontrado.html";
		}
	}

	$arquivo = new Arquivo($caminho);

	switch ($arquivo->ext) {
		case "php":
			include($arquivo->path);
			break;
		default:
			$arquivo->renderiza();
			break;
	}
}

/**
 * Função para escrever na tela o json de configurações para o front-end
 * (função faz um echo)
 *
 * @return void
 */
function getVarsApp()
{
	$config = new Config();
	echo json_encode($config->getConfigApp());
}
