<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config extends Arquivo
{
	//config do app
	public $nomeApp = "Lis";
	public $ambiente = "local";
	public $debug = false;

	private $config = [];

	/**
	 * Função contrutura para setar o arquivo de config
	 *
	 * @return void
	 */
	public function __construct()
	{
		if (file_exists($this->getAmbiente())) {
			parent::__construct($this->getAmbiente());
			$this->config = $this->ler();
		} else {
			parent::__construct("security/environment/config.json");
			$this->config = $this->ler();
			parent::__construct($this->getAmbiente(), true);
			$this->escrever($this->config);
		}

		$this->nomeApp = $this->config['app']['nome'];
		$this->ambiente = $_SERVER["HTTP_HOST"];
		$this->debug = $this->config['debug'];
	}

	/**
	 * Configurar variaveis do banco
	 *
	 * @return array - dados do banco
	 */
	public function getConfigBanco()
	{
		$retorno = $this->config['banco'];
		$retorno['stringConn'] = "mysql:host={$retorno["host"]}:{$retorno["porta"]};dbname={$retorno["nome"]}";
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
}
