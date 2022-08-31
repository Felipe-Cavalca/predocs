<?php

include_once("security/core/Funcoes.php");
include_once("security/core/Arquivo.php");
include_once("security/core/Config.php");
include_once("security/core/Banco.php");
include_once("security/core/Storage.php");
include_once("security/core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

configPHP();
$_POST = post();

$arquivo = new Arquivo(getUrl($_GET['_Pagina'] ?? "index"));
$arquivo->renderiza();

function getUrl($url)
{
	switch (explode("/", $url)[0]) {
		case "varsApp":
			$config = new Config();
			return "{$config->getPathEnvironment()}/config/app.json";
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
			if (file_exists($url)) {
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
	$config = new Config();

	criaPasta("./security/storage/files");
	criaPasta("{$config->getPathEnvironment()}/cache/session");

	session_save_path("{$config->getPathEnvironment()}/cache/session");

	ini_set("memory_limit", "5G");

	if ($config->debug) {
		ini_set("display_errors", 1);
	} else {
		ini_set("display_errors", 0);
	}
}

/**
 * Função para validar se os dados estão vindo via json ou form-encode
 */
function post()
{
	$json = json_decode(file_get_contents('php://input'), true);
	return (is_array($json) ? $json : $_POST);
}
