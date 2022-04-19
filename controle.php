<?php

//incluindo as classes
include_once("security/core/Arquivo.php");
include_once("security/core/Config.php");
include_once("security/core/Banco.php");
include_once("security/core/Funcoes.php");

try {
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
} catch (Exception $e) {
	renderiza("error/internal-server.html", false);
}


/**
 * Função para renderizar um arquivo na tela
 *
 * @param string - caminho do arquivo a ser renderizado
 * @param boolean - diz se o arquivo está na pasta segura ou não
 * @return void
 */
function renderiza($arquivo, $security = true)
{
	if($security){
		$arquivo = new Arquivo("security/" . $arquivo);
	}else{
		$arquivo = new Arquivo($arquivo);
	}

	if ($arquivo->path == false) {
		$arquivo = new Arquivo("error/nao-encontrado.html");
	}

	header("Content-Type: " . $arquivo->mime);
	echo $arquivo->ler();
}
