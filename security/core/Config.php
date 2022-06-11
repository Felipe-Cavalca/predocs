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
		if (file_exists($this->getAmbienteFile())) {
			parent::__construct($this->getAmbienteFile());
			$this->config = $this->ler();
		} else {
			$arquivoConfig = new Arquivo("security/environment/config.json");
			$this->config = $arquivoConfig->ler();

			$arquivo = new Arquivo($this->getAmbienteFile(), true);
			$arquivo->escrever($this->config);
		}

		$cacheConfigApp = new Arquivo("security/cache/configApp.json", !file_exists("security/cache/configApp.json"));
		$cacheConfigApp->escrever($this->getConfigApp());

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
		$retorno["tipo"] = $this->config["banco"]["tipo"];
		$retorno["nome"] = $this->config["banco"]["nome"];
		switch ($this->config["banco"]["tipo"]) {
			case "sqlite":
				$retorno["stringConn"] = "sqlite:security/{$this->config["banco"]["nome"]}.db";
				break;
			case "mysql":
				$retorno["credenciais"] = $this->config["banco"]["mysql"]["credenciais"];
				$retorno["stringConn"] = "mysql:host={$this->config["banco"]["mysql"]["host"]}:{$this->config["banco"]["mysql"]["porta"]};dbname={$this->config["banco"]["nome"]}";
				break;
		}
		return $retorno;
	}

	/**
	 * Função para pegar o caminho do arquivo json do ambiente em que o servidor está rodando
	 * @return string
	 */
	function getAmbienteFile()
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
