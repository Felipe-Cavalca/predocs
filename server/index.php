<?php

$_BANCO = [
	"tipo" => null,
	"conexao" => null
];

//inclui os arquivos do core
include_once("./core/Funcoes.php");
include_once("./core/Arquivo.php");
include_once("./core/Config.php");
include_once("./core/Banco.php");
include_once("./core/Storage.php");
include_once("./core/Log.php");
include_once("./core/FuncoesApp.php"); //funcoes da aplicação que está sendo desenvolvida

$funcoes = new funcoes();

//inicia o framework e escreve sua saida
$retorno = $funcoes->init() ?? "Não foi possivel recuperar a saida da função";

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
