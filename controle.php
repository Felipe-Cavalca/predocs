<?php

//alterando local onde salva as sessões
session_save_path("./security/session");

//incluindo as classes
include_once("security/core/Funcoes.php");
include_once("security/core/Arquivo.php"); //arquivo estende a funções
include_once("security/core/Config.php"); //config estende a arquivo
include_once("security/core/Banco.php"); //banco estende a config
include_once("security/core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

//pega os dados do post
$_POST = json_decode(file_get_contents("php://input"), true);

try {
	if (isset($_GET['_Pagina'])) {
		switch (explode("/", $_GET['_Pagina'])[0]) {
			case "varsApp":
				getVarsApp(); //paga as variaveis do app
				break;
			case "lis":
				controleAdmin($_GET['_Pagina']); //executa a parte de gerenciamento do framework
				break;
			case "storage":
				storage(); //chama o controle do storage
				break;
			case "api":
			case "server":
				retornar("security/server/index.php"); //chama o index do server (backend)
				break;
			case "coreJs":
				retornar("security/web/core/index.js"); //retorna o arquivo do coreJs
				break;
			case "coreCss":
				retornar("security/web/core/index.css"); //retorna o arquivo do coreCss
				break;
			case "framework":
			case "midia":
			case "components":
			case "css":
			case "js":
				retornar("security/web/" . $_GET["_Pagina"]); //retorna paginas do web
				break;
			default:
				retornar("security/web/pages/" . $_GET["_Pagina"]); //retorna o arquivo dentro do pages
				break;
		};
	} else {
		retornar("security/web/pages/index.html"); //retorna o index da aplicação
	}
} catch (Exception $e) {
	retornar("error/internal-server.html");	 //caso o sistema de alguma exception retona a pagina de erro do servidor
}

/**
 * Função para retornar o arquivo que foi solicitado ao usuario
 *
 * @param string - caminho do arquivo a ser renderizado
 * @return void
 */
function retornar(string $caminho)
{
	//valida se o arquivo existe
	if (!file_exists($caminho)) {
		//caso o arquivo não exista, adiciona o .html para ver se é um arquivo.html
		$caminho .= ".html";
	}

	//valida se o arquivo existe
	if (!file_exists($caminho)) {
		//caso o arquivo não exista, retorna a pagina de não encontrado de sua extenção
		$arquivoErro = "error/not-found/nao-encontrado." . getExt($caminho);
		if (file_exists($arquivoErro)) {
			$caminho = $arquivoErro; //caso o arquivo de erro exista ele será renderizado
		} else {
			$caminho = "error/not-found/nao-encontrado.html"; //caso não exista ele retorna o erro .html
		}
	}

	//instancia a classe arquivo
	$arquivo = new Arquivo($caminho);

	//renderiza o arquivo
	$arquivo->renderiza();
	return;
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
	return;
}

/**
 * Função para importar o controle da parte administrativa
 * @param string $url - a url pega pelo $_GET['_Pagina']
 * @return void
 */
function controleAdmin(string $url)
{
	$arquivo = new Arquivo("security/admin/controleAdmin.php"); //inclui o arquivo de controle admin
	$arquivo->renderiza(); //renderiza o arquivo
	urlAdmin($url); //valida a url do admin
	return;
}

/**
 * Função para redirecionar o usuario para o controle de storage
 * @return void
 */
function storage()
{
	$arquivo = new Arquivo("security/storage/controleStorage.php"); //inclui o controle de storage
	$arquivo->renderiza(); //renderiza o arquivo
	return;
}
