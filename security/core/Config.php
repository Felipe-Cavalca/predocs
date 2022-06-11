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
		$arquivo = "security/config/{$arquivo}.json";
		return (file_exists($arquivo) ? $arquivo : "");

		// return "security/cache/{$_SERVER["HTTP_HOST"]}.json";
		// return "security/environment/" . $_SERVER["HTTP_HOST"] . ".json";
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
			case "sqlite":
				$retorno["stringConn"] = "sqlite:security/database/{$config["nome"]}.db";
				break;
			case "mysql":
				$retorno["credenciais"] = $config["mysql"]["credenciais"];
				$retorno["stringConn"] = "mysql:host={$config["mysql"]["host"]}:{$config["mysql"]["porta"]};dbname={$config["nome"]}";
				break;
		}
		return $retorno;
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
}
