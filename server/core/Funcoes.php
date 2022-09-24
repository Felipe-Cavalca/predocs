<?php

/**
 * Funções para gerenciamento do framework
 */
class funcoes
{
	/**
	 * Função para printar algo na tela
	 *
	 * @param mixed $data
	 * @return string
	 */
	function pr(mixed $data)
	{
		echo '<pre>' . print_r($data, true) . '</pre>';
	}

	/**
	 * Função para listar os arquivos de uma pasta
	 *
	 * @param string $path - caminho da lista de pastas
	 * @return array - array com os nomes dos arquivos/pastas de dentro do diretorio
	 */
	function listarArquivos(string $path = '/')
	{
		$diretorio = dir($path);
		$arquivos = [];
		while ($arquivo = $diretorio->read()) {
			$arquivos[] = $arquivo;
		}
		$diretorio->close();
		return $arquivos;
	}

	/**
	 * Função para validar se os campos existem
	 * @param array $campos - indice dos campos dentro do $_POST
	 * @return ["status" => boolean, "msg" => string]
	 */
	function issetPost(array $campos = [])
	{
		foreach ($campos as $campo) {
			if (!isset($_POST[$campo])) {
				return [
					"status" => false,
					"msg" => "Campo '{$campo}' não encontrado"
				];
			}
		}

		return [
			"status" => true,
			"msg" => "Todos os campos existem"
		];
	}

	/**
	 * Função para validar se os campos não são vazios
	 * @param array $campos array com as strings a serem validadasimage.pngval
	 * @return ["status" => boolean, "msg" => string]
	 */
	function emptyPost(array $campos = [])
	{
		foreach ($campos as $campo) {
			if (empty($_POST[$campo])) {
				return [
					"status" => true,
					"msg" => "Campo '{$campo}' está vazio"
				];
			}
		}

		return [
			"status" => false,
			"msg" => "Todos os campos estão ok"
		];
	}

	/**
	 * Valida se uma pasta existe, caso não exista cria a mesma
	 */
	function criaPasta(string $path, int $permission = 0777)
	{
		if (!is_dir($path)) return mkdir($path, $permission, true);
	}

	/**
	 * Função para setar as config do php
	 * @return void
	 */
	function configPHP()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
		// header("Content-Type: application/json");
		// header("Content-Type: text/plain"); //para teste

		$config = new Config();

		$this->criaPasta($config->getCaminho("storage"));
		$this->criaPasta($config->getCaminho("session"));

		session_save_path($config->getCaminho("session"));

		ini_set("memory_limit", "5G"); //pegar do config

		ini_set("display_errors", $config->debug ? "1" : "0");
	}

	/**
	 * Função para validar se os dados estão vindo via json ou form-encode
	 * @return void
	 */
	function post()
	{
		$json = json_decode(file_get_contents('php://input'), true);
		$_POST = (is_array($json) ? $json : $_POST);
	}

	/**
	 * Função para organizar os dados do get
	 * @return void
	 */
	function get()
	{
		$retorno = [];
		$url = explode("/", isset($_GET["_Pagina"]) ? $_GET["_Pagina"] : "");
		$retorno["controller"] = isset($url[0]) ? $url[0] : null;
		$retorno["function"] = empty($url[1]) ? "index" : $url[1];
		$retorno["param"] = isset($url[2]) ? $url[2] : null;
		$_GET = $retorno;
	}

	/**
	 * Retorna para quem está fazendo a solicitação o erro 404
	 * @return void
	 */
	function naoEncontrado()
	{
		http_response_code(404);
		echo json_encode(["status" => false, "msg" => "A função solicitada não foi encontrada"]);
	}

	/**
	 * inclui um controller
	 * caso haja uma classe, retorna o mesmo
	 * caso não haja - true e inclui o arquivo
	 * @param string $nome - nome do controller
	 * @return obj|boolean
	 */
	function incluiController(string $nomeController)
	{
		$config = new Config;
		$controller = new Arquivo("{$config->getCaminho("controller")}/{$nomeController}Controller.php");

		switch ($nomeController) {
			case "autorun":
				$controller = new Arquivo("{$config->getCaminho("functions")}/autorun.php");
			default:
				if ($controller->existe()) {
					$controller->renderiza();
					if (class_exists($nomeController)) {
						return new $nomeController();
					}
					return true;
				} else {
					return false;
				}
		}
	}
}
