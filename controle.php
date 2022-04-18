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
		break;
	case "framework":
		break;
	default:
		break;
};
