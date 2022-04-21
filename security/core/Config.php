<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config extends Arquivo
{
	//config do app
	public $nomeApp = "Lis";
	public $ambiente = "local";

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
			parent::__construct("security/ambientes/config.json");
			$this->config = $this->ler();
			parent::__construct($this->getAmbiente(), true);
			$this->escrever($this->config);
		}

		$this->nomeApp = $this->config['app']['nome'];
		$this->ambiente = $this->config['ambiente'];
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
		return "security/ambientes/" . $_SERVER["HTTP_HOST"] . ".json";
	}
}
