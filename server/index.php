<?php

//inclui os arquivos do core
include_once("./core/Funcoes.php");
include_once("./core/Arquivo.php");
include_once("./core/Config.php");
include_once("./core/Banco.php");
include_once("./core/Storage.php");
include_once("./core/Log.php");
include_once("./core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

$Funcoes = new funcoes();

//inicia o framework e escreve sua saida
$retorno = $Funcoes->init() ?? "erro ao recuperar saida da função";

switch (gettype($retorno)) {
	case "array":
		echo json_encode($retorno);
		break;
	case "integer":
	case "string":
		echo json_encode(["retorno" => $retorno]);
		break;
	case "boolean":
		echo json_encode(["status" => $retorno]);
		break;
	default:
		echo $retorno;
}
