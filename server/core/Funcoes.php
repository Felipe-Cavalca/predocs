<?php

/**
 * Funções para gerenciamento do framework
 */
class funcoes
{

	/**
	 * funcao para analizar a request de um usuario
	 * @version 1
	 * @access public
	 * @return mixed
	 */
	public function init(): mixed
	{
		$this->configPHP(); //seta as config do php
		$this->post(); //organiza o $_POST
		$this->get(); //organiza o $_GET

		if (empty($_GET["controller"])) return $this->naoEncontrado();

		$controller = $this->incluiController($_GET["controller"]);

		switch (gettype($controller)) {
			case "integer":
				if ($controller == 404) return $this->naoEncontrado();
				if ($controller == 401) return $this->semAutorizacao();
				if ($controller == 200 && function_exists($_GET["function"]))
					return call_user_func($_GET["function"], $_GET["param"]);
				else return $this->naoEncontrado();
				break;
			case "object":
				if (method_exists($controller, $_GET["function"]))
					return call_user_func([$controller, $_GET["function"]], $_GET["param"]);
				else return $this->naoEncontrado();
				break;
		}

		return $this->erroInterno();
	}

	/**
	 * Função para listar os arquivos de uma pasta
	 * @version 1
	 * @access public
	 * @param string $pasta caminho da lista de pastas
	 * @return array array com os nomes dos arquivos/pastas de dentro do diretorio
	 */
	public function listarArquivos(string $pasta = '/'): array
	{
		$diretorio = dir($pasta);
		$arquivos = [];
		while ($arquivo = $diretorio->read()) {
			$arquivos[] = $arquivo;
		}
		$diretorio->close();
		return $arquivos;
	}

	/**
	 * Valida se campos existem em um array
	 * @version 1
	 * @access public
	 * @param array $indices nome dos campos a serem verificados
	 * @param array $array array a ser verificado
	 * @return array ["status" => boolean, "msg" => string]
	 */
	public function isset(array $indices = [], array $array = []): array
	{
		if (empty($array)) $array = $_POST;
		foreach ($indices as $indice) {
			if (!isset($array[$indice])) {
				return ["status" => false, "msg" => "indice '{$indice}' não encontrado"];
			}
		}
		return ["status" => true, "msg" => "Todos os indices existem"];
	}

	/**
	 * Valida se indices de um array não estão vazios
	 * @version 1
	 * @access public
	 * @param array $indices nome dos indices a serem verificados
	 * @param array $array array a ser verificado
	 * @return ["status" => boolean, "msg" => string]
	 */
	public function empty(array $indices = [], array $array = []): array
	{
		if (empty($array)) $array = $_POST;
		foreach ($indices as $indice) {
			if (empty($array[$indice])) {
				return ["status" => true, "msg" => "Campo '{$indice}' está vazio"];
			}
		}

		return ["status" => false, "msg" => "Todos os campos estão ok"];
	}

	/**
	 * Valida se uma pasta existe, caso não exista cria a mesma
	 * @version 1
	 * @access public
	 * @param string $caminho - caminho até a pasta
	 * @param int $permission - permissão da pasta
	 * @return bool
	 */
	public function criaPasta(string $path, int $permission = 0777): bool
	{
		if (!is_dir($path)) return mkdir($path, $permission, true);
		return false;
	}

	/**
	 * Função para setar as config do php
	 * @version 1
	 * @access public
	 * @return void
	 */
	private function configPHP(): void
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

		ini_set("display_errors", "1");

		return;
	}

	/**
	 * Função para validar se os dados estão vindo via json ou form-encode
	 * @version 1
	 * @access public
	 * @return void
	 */
	private function post(): void
	{
		$json = json_decode(file_get_contents('php://input'), true);
		$_POST = (is_array($json) ? $json : $_POST);
		return;
	}

	/**
	 * Função para organizar os dados do get
	 * @version 1
	 * @access public
	 * @return void
	 */
	private function get(): void
	{
		$retorno = [];
		$url = explode("/", isset($_GET["_Pagina"]) ? $_GET["_Pagina"] : "");
		$retorno["controller"] = isset($url[0]) ? $url[0] : null;
		$retorno["function"] = empty($url[1]) ? "index" : $url[1];
		$retorno["param"] = isset($url[2]) ? $url[2] : null;
		$_GET = $retorno;
		return;
	}

	/**
	 * Retorna para quem está fazendo a solicitação o erro 404
	 * @version 1
	 * @access public
	 * @return array
	 */
	public function naoEncontrado(): array
	{
		http_response_code(404);
		return ["status" => false, "msg" => "A função solicitada não foi encontrada"];
	}

	/**
	 * Chamado para paginas em que o usuario não tem permissão para acessar
	 * @version 1
	 * @access public
	 * @return array
	 */
	public function semAutorizacao(): array
	{
		http_response_code(401);
		return ["status" => false, "msg" => "Acesso negado"];
	}

	/**
	 * Função caso haja um erro interno no sistema (500)
	 * @version 1
	 * @access public
	 * @return array
	 */
	public function erroInterno(): array
	{
		http_response_code(500);
		return ["status" => false, "msg" => "Erro interno"];
	}

	/**
	 * inclui um controller
	 * @version 1
	 * @access public
	 * @param string $nome nome do controller
	 * @return object|int obj para o controller, int com o status da importação
	 * caso haja uma classe, retorna o mesmo
	 * caso não haja - true e inclui o arquivo
	 */
	public function incluiController(string $nomeController): mixed
	{
		$config = new Config;
		$controller = new Arquivo("{$config->getCaminho("controller")}/{$nomeController}Controller.php");

		//valida se o controller não é uma função do framework
		switch ($nomeController) {
			case "autorun":
				$controller = new Arquivo("{$config->getCaminho("functions")}/autorun.php");
				break;
		}

		if (!$controller->existe()) return 404;
		$controller->renderiza();
		if (!class_exists($nomeController)) return 200;
		$obj = new $nomeController();
		if (method_exists($obj, "__autorizado") && $obj->__autorizado($_GET["function"]) === false) return 401;
		return $obj;
	}
}

/**
 * Função para printar algo na tela
 * @version 1
 * @access public
 * @param mixed $data
 * @return void
 */
function pr(mixed $data): void
{
	echo '<pre>' . print_r($data, true) . '</pre>';
}
