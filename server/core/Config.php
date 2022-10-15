<?php

//classe de config extende a classe arquivo para que consiga ler os arquivos de configuração
class Config
{
	/**
	 * Função para pegar o caminho do arquivo json do ambiente em que o servidor está rodando
	 * @version 1
	 * @access public
	 * @param string $nome do arquivo de config
	 * @return Arquivo objeto arquivo
	 */
	public function fileConfig(string $arquivo = ""): Arquivo
	{
		$arquivoConfig = new Arquivo("{$this->getCaminho("config")}/{$arquivo}.json");
		if ($arquivoConfig->existe() !== false)
			return $arquivoConfig;

		$modelo = new Arquivo("{$this->getCaminho("model/config")}/{$arquivo}.json");
		$arquivoConfig->criar();
		$arquivoConfig->escrever(conteudo: $modelo->ler());
		return $arquivo;
	}

	/**
	 * Pegar as variaveis da base de dados
	 * @version 1
	 * @access public
	 * @return array config de conexao
	 */
	public function getConfigBanco(): array
	{
		$config = $this->fileConfig(arquivo: "banco")->ler();

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
	 * Salvar variaveis da base de dados
	 * @version 1
	 * @access public
	 * @param array $config configurações a serem salvas
	 * @return bool
	 */
	public function setConfigBanco(array $configs): bool
	{
		return $this->fileConfig("banco")->adicionar($configs);
	}

	/**
	 * Função para pegar as configurações de cache
	 * @version 1
	 * @access public
	 * @return array|string array com os dados de cache
	 */
	public function getConfigCache(): array|string
	{
		return $this->fileConfig("cache")->ler();
	}

	/**
	 * Função para pegar as configurações
	 * @version 1
	 * @access public
	 * @return array|string array com os dados de config
	 */
	public function getConfig(): array
	{
		return $this->fileConfig("config")->ler();
	}

	/**
	 * Função para pegar o caminho até a pasta de ambiente
	 * @version 1
	 * @access public
	 * @return string
	 */
	public function ambiente(): string
	{
		return $_SERVER["HTTP_HOST"];
	}

	/**
	 * Função para pegar o caminho até um diretori
	 * @version 1
	 * @access public
	 * @param string $tipo nome da pasta que será exibida
	 * @return string caminho até a pasta selecionada
	 */
	public function getCaminho(string $tipo = ""): string
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
				return "./data/{$this->ambiente()}/{$tipo}";
			case "sqlite":
				return "./data/{$this->ambiente()}/database";
			case "sql":
			case "functions":
			case "includes":
			default:
				return "./{$tipo}";
		}
	}
}
