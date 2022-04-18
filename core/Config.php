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
		parent::__construct("core/config.json");

		$this->config = $this->ler();

		if (!empty($this->config['app']['nome'])) {
			$this->nomeApp = $this->config['app']['nome'];
		}

		if (!empty($this->config['ambiente'])) {
			$this->ambiente = $this->config['ambiente'];
		}
	}

	/**
	 * Configurar variaveis do banco
	 *
	 * @return array - dados do banco
	 */
	public function getConfigBanco()
	{
		$retorno = $this->config['banco'][$this->ambiente];
		$retorno['stringConn'] = "mysql:host={$retorno["host"]}:{$retorno["porta"]};dbname={$retorno["nome"]}";
		return $retorno;
	}
}
