<?php

//incluindo as classes
include_once("core/Arquivo.php");
include_once("core/Config.php");
include_once("core/Banco.php");
include_once("core/Funcoes.php");

//valida se a url existe
if (isset($_GET['_Pagina'])) {
	$url = explode("/", $_GET['_Pagina']);
} else {
	$url[0] = null;
}

switch ($url[0]) {
	case "api":
	case "server":
		break;
		break;
	case "coreJs":
		renderiza("web/core/index.js");
		break;
	case "coreCss":
		renderiza("web/core/index.css");
		break;
	case "framework":
	case "midia":
	case "component":
	case "css":
	case "js":
		renderiza("web/" . $_GET["_Pagina"]);
		break;
	default:
		if (isset($_GET["_Pagina"])) {
			renderiza("web/pages/" . $_GET["_Pagina"]);
		} else {
			renderiza("web/pages/index.html");
		}
		break;
};


/**
 * Função para renderizar um arquivo na tela
 *
 * @param - caminho do arquivo a ser renderizado
 * @return void
 */
function renderiza($arquivo)
{
	$arquivo = new Arquivo($arquivo);
	header("Content-Type: " . $arquivo->mime);
	echo $arquivo->ler();
}
