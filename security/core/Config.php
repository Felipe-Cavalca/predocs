<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config extends Arquivo
{
	//config do app
	public $nomeApp = "Lis";
	public $ambiente = "local";
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
		if (file_exists($this->getAmbiente())) {
			parent::__construct($this->getAmbiente(), false, false);
			$this->config = $this->ler();
		} else {
			parent::__construct("security/environment/config.json", false, false);
			$this->config = $this->ler();
			parent::__construct($this->getAmbiente(), true, false);
			$this->escrever($this->config);
		}

		$this->nomeApp = $this->config['app']['nome'];
		$this->ambiente = $_SERVER["HTTP_HOST"];
		$this->debug = $this->config['debug'];
		$this->url = $this->config['app']['url'];
	}

	/**
	 * Configurar variaveis do banco
	 *
	 * @return array - dados do banco
	 */
	public function getConfigBanco()
	{
		$config = $this->config["banco"];
		$retorno["tipo"] = $config["tipo"];
		$retorno["nome"] = $config["nome"]
		switch ($config["tipo"]) {
			case "sqlite":
				$retorno["stringConn"] = "sqlite:security/" . $config["nome"] . ".db";
				break;
			case "mysql":
				$retorno["credenciais"] = $config["mysql"]["credenciais"];
				$retorno["stringConn"] = "mysql:host={$config["mysql"]["host"]}:{$config["mysql"]["porta"]};dbname={$config["nome"]}";
				break;
		}
		return $retorno;
	}

	/**
	 * Função para pegar o caminho do arquivo json do ambiente em que o servidor está rodando
	 * @return string
	 */
	function getAmbiente()
	{
		return "security/environment/" . $_SERVER["HTTP_HOST"] . ".json";
	}

	/**
	 * Função para pegar as variaveis do app
	 * @return array array de configurações a serem enviadas para o front-end
	 */
	function getConfigApp()
	{
		return $this->config["app"];
	}

	/**
	 * Retorna dados da parte administrativa do framework
	 *
	 * @return array array de configurações
	 */
	function getConfigAdmin()
	{
		return $this->config["admin"];
	}

	/**
	 * Função para pegar as configurações de cache
	 *
	 * @return array - array com os dados de cache
	 */
	function getconfigCache()
	{
		return $this->config["cache"];
	}
}
