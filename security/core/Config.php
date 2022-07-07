<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config extends Arquivo
{
	public $debug = false;
	public $url = "http://localhost";

	private $config = [];

	/**
	 * Função contrutura para setar o arquivo de config
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->debug = $this->getConfig()["debug"];
		$this->url = $this->getConfigApp()["url"];
	}

	/**
	 * Função para pegar o caminho do arquivo json do ambiente em que o servidor está rodando
	 * @return string
	 */
	function fileConfig($arquivo = "")
	{
		$file = "{$this->getPathEnvironment()}/config/{$arquivo}.json";
		if (file_exists($file)) {
			return $file;
		}

		$origin = new Arquivo("security/config/{$arquivo}.json", false);
		$new = new Arquivo($file, true);
		$new->escrever($origin->ler());
		return $file;
	}

	/**
	 * Retorna dados da parte administrativa do framework
	 *
	 * @return array array de configurações
	 */
	function getConfigAdmin()
	{
		$this->path = $this->fileConfig("admin");
		return $this->ler();
	}

	/**
	 * Função para pegar as variaveis do app
	 * @return array array de configurações a serem enviadas para o front-end
	 */
	function getConfigApp()
	{
		$this->path = $this->fileConfig("app");
		return $this->ler();
	}

	/**
	 * Configurar variaveis do banco
	 *
	 * @return array - dados do banco
	 */
	public function getConfigBanco()
	{
		$this->path = $this->fileConfig("banco");
		$config = $this->ler();

		$retorno["tipo"] = $config["tipo"];
		$retorno["nome"] = $config["nome"];
		switch ($config["tipo"]) {
			case "mysql":
				$retorno["credenciais"] = $config["mysql"]["credenciais"];
				$retorno["stringConn"] = "mysql:host={$config["mysql"]["host"]}:{$config["mysql"]["porta"]};dbname={$config["nome"]}";
				break;
			case "sqlite":
			default:
				$retorno["stringConn"] = "sqlite:{$this->getPathEnvironment()}/database/{$config["nome"]}.db";
				break;
		}
		return $retorno;
	}

	/**
	 * Salvar variaveis no banco
	 *
	 * @param array $config - configurações a serem salvas
	 */
	public function setConfigBanco($configs){
		$this->path = $this->fileConfig("banco");
		$this->escrever($configs);
	}

	/**
	 * Função para pegar as configurações de cache
	 *
	 * @return array - array com os dados de cache
	 */
	function getConfigCache()
	{
		$this->path = $this->fileConfig("cache");
		return $this->ler();
	}

	/**
	 * Função para pegar as configurações
	 *
	 * @return array - array com os dados de cache
	 */
	function getConfig()
	{
		$this->path = $this->fileConfig("config");
		return $this->ler();
	}

	/**
	 * Função para pegar o caminho até a pasta de ambiente
	 * function for the get path environment
	 * @return string
	 */
	function getPathEnvironment()
	{
		$name = $_SERVER["HTTP_HOST"];
		// $name = "Teste";
		$path = "./data/{$name}";
		return $path;
	}
}
