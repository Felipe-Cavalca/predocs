<?php

include_once("security/core/Funcoes.php");
include_once("security/core/Arquivo.php");
include_once("security/core/Config.php");
include_once("security/core/Banco.php");
include_once("security/core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

configPHP();

$arquivo = new Arquivo(getUrl($_GET['_Pagina'] ?? "index"));
$arquivo->renderiza();

function getUrl($url)
{
	switch (explode("/", $url)[0]) {
		case "varsApp":
			return "security/config/app.json";
		case "lis":
			return "security/admin/controleAdmin.php";
		case "storage":
			return "security/storage/controleStorage.php";
		case "api":
		case "server":
			return "security/server/index.php";
		case "coreJs":
			return "security/web/core/index.js";
		case "coreCss":
			return "security/web/core/index.css";
		case "framework":
		case "midia":
		case "components":
		case "css":
		case "js":
			return "security/web/{$url}";
		case "error":
			return $url;
		case "index":
			return "security/web/pages/index.html";
		default:
			$caminho = "security/web/pages/{$url}";
			if (file_exists($caminho . ".html")) {
				return $caminho . ".html";
			} else if (file_exists($caminho . "index.html")) {
				return $caminho . "index.html";
			} else if (file_exists($caminho . "/index.html")) {
				return $caminho . "/index.html";
			} else if (file_exists($caminho) && !is_dir($caminho)) {
				return $caminho;
			}
			if(file_exists($url)){
				return $url;
			}
	}

	return false;
}

/**
 * Função para as config do php
 */
function configPHP()
{
	criaPasta("./security/cache");
	criaPasta("./security/storage/files");
	criaPasta("./security/cache/session");

	session_save_path("./security/cache/session");

	$config = new Config();
	if ($config->debug) {
		ini_set("display_errors", 1);
	} else {
		ini_set("display_errors", 0);
	}
}
