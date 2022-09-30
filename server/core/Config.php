<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config extends Arquivo
{
	public $debug = true;

	/**
	 * Função contrutura para setar o arquivo de config
	 *
	 * @return void
	 */
	public function __construct()
	{
		// $this->debug = $this->getConfig()["debug"];
	}

	/**
	 * Função para pegar o caminho do arquivo json do ambiente em que o servidor está rodando
	 * @param string nome do arquivo de config
	 * @return obj - objeto arquivo
	 */
	function fileConfig(string $arquivo = "")
	{
		$file = "{$this->getCaminho("config")}/{$arquivo}.json";
		if (file_exists($file)) {
			return new Arquivo($file);
		}

		$origin = new Arquivo("{$this->getCaminho("model/config")}/{$arquivo}.json", false);
		$new = new Arquivo($file, true);
		$new->escrever($origin->ler());
		return $new;
	}

	/**
	 * Pegar as variaveis da base de dados
	 *
	 * @return array - dados do banco
	 */
	public function getConfigBanco()
	{
		$config = $this->fileConfig("banco")->ler();

		$retorno["tipo"] = $config["tipo"];
		$retorno["nome"] = $config["nome"];
		$retorno["instalado"] = $config["instalado"];
		switch ($config["tipo"]) {
			case "mysql":
				$retorno["credenciais"] = $config["mysql"]["credenciais"];
				$retorno["stringConn"] = "mysql:host={$config["mysql"]["host"]}:{$config["mysql"]["porta"]};dbname={$config["nome"]}";
				break;
			case "sqlite":
			default:
				$retorno["stringConn"] = "sqlite:{$this->getCaminho("sqlite")}/{$config["nome"]}.db";
				break;
		}
		return $retorno;
	}

	/**
	 * Salvar variaveis no banco
	 *
	 * @param array $config - configurações a serem salvas
	 * @return boolean
	 */
	public function setConfigBanco(array $configs)
	{

		return $this->fileConfig("banco")->adicionar($configs);
	}

	/**
	 * Função para pegar as configurações de cache
	 *
	 * @return array|string - array com os dados de cache
	 */
	function getConfigCache()
	{
		return $this->fileConfig("cache")->ler();
	}

	/**
	 * Função para pegar as configurações
	 *
	 * @return array|string - array com os dados de cache
	 */
	function getConfig()
	{
		return $this->fileConfig("config")->ler();
	}

	/**
	 * Função para pegar o caminho até a pasta de ambiente
	 * @return string
	 */
	function getCaminhoEnvironment()
	{
		$name = $_SERVER["HTTP_HOST"];
		// $name = "Teste";
		$path = "./data/{$name}";
		return $path;
	}

	/**
	 * Função para listar algum diretorio
	 * @param string $tipo - nome da pasta que será exibida
	 * @return string - caminho até a pasta selecionada
	 */
	function getCaminho(string $tipo = "")
	{
		switch ($tipo) {
			case "controller":
				return "./controllers";
			case "model/config":
				return "./models/config";
			case "log":
			case "config":
			case "storage":
			case "session":
				return "./data/{$tipo}";
			case "sqlite":
				return "./data/database";
			case "sql":
			case "functions":
			case "includes":
			default:
				return "./{$tipo}";
		}
	}

	// /**
	//  * Retorna dados da parte administrativa do framework
	//  *
	//  * @return array array de configurações
	//  */
	// function getConfigAdmin()
	// {
	// 	$this->path = $this->fileConfig("admin");
	// 	return $this->ler();
	// }

	/**
	 * Função para pegar as variaveis do app
	 * @return array array de configurações a serem enviadas para o front-end
	 */
	// function getConfigApp()
	// {
	// 	$this->path = $this->fileConfig("app");
	// 	return $this->ler();
	// }

}
