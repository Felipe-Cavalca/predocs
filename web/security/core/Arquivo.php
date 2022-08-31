<?php

/**
 * Classe destinada a manipulação de arquivo
 */
class Arquivo
{
	public $path; //caminho até o arquivo (inclui o nome)

	/**
	 * Função construtora do arquivo
	 *
	 * @param string - caminho até o arquivo
	 * @param bool - caso o parametro seja true, um novo arquivo será criado
	 */
	public function __construct(string $arquivo, bool $novo = false)
	{
		if ($arquivo == null) {
			$arquivo = "error/index.html";
		}

		if ($novo) {
			$arrayArquivo = explode("/", $arquivo);
			unset($arrayArquivo[count($arrayArquivo) - 1]);
			$dir = implode("/", $arrayArquivo);
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			$arq = fopen($arquivo, 'w');
			fclose($arq);
		}

		$this->path = $arquivo;
	}

	public function upload($arquivo, $destino)
	{
		return $this->mover($arquivo, $destino);
	}

	public function mover($origem, $destino)
	{
		return move_uploaded_file($origem, $destino);
	}

	/**
	 * Função para pegar o conteudo de um arquivo
	 *
	 * @return array - caso seja um arquivo .json
	 * @return string - conteudo do arquivo
	 */
	public function ler()
	{
		switch ($this->getExt()) {
			case 'json': //caso json chama a função lerJson
				return $this->lerJson();
			default: //função para ler arquivo
				return $this->lerArquivo();
		}
	}

	/**
	 * Função para escrever no arquivo
	 *
	 * @param string string a ser guardada no arquivo
	 * @param array array a ser salvo em caso de arquivos json
	 * @return boolean
	 */
	public function escrever($conteudo)
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->escreverJson($conteudo);
			default:
				return $this->escreverArquivo($conteudo);
		}
	}

	/**
	 * adiciona conteudo ao final do arquivo
	 *
	 * @param string - adiciona conteudo no final do arquivo
	 * @param array - caso o arquivo seja um json
	 * @return void
	 */
	public function adicionar($conteudo)
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->escreverJson(array_merge($this->ler(), $conteudo));
			default:
				return $this->escreverArquivo($this->lerArquivo() . $conteudo);
		}
	}

	/**
	 * Função para ler e exibir o conteudo de um arquivo na tela
	 */
	public function renderiza()
	{
		switch ($this->getExt()) {
			case "php":
				include_once($this->path);
				break;
			default:
				header("Content-Type: " . $this->getMimeType());
				header("Cache-Control: " . $this->tempoCache());
				readfile($this->path);
				break;
		}
	}

	/**
	 * Função para apagar um arquivo
	 * @return boolean - diz se o arquivo foi apagado ou não
	 */
	public function apagar()
	{
		return unlink($this->path);
	}

	/**
	 * Função para pegar o tempo de cache do arquivo
	 * @return string - cache a ser colocado no arquivo
	 */
	function tempoCache()
	{
		$config = new Config;
		$cache = $config->getconfigCache();

		if (!$cache["ativo"] || isset($cache["excluir"][$this->getExt()])) {
			return "no-cache";
		}

		if (isset($cache["incluir"][$this->getExt()])) {
			return "private, max-age=" . $cache['incluir'][$this->getExt()] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
		}

		return "private, max-age=" . $cache['default'] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
	}

	/**
	 * Retorna o mimetype do arquivo
	 *
	 * @return string - mimetype do arquivo
	 */
	function getMimeType()
	{
		$arquivo = $this->path;
		if (file_exists($arquivo)) {
			switch ($this->getExt()) {
				case "js":
					return "application/javascript";
				case "css":
					return "text/css";
				default:
					return mime_content_type($arquivo);
			}
		} else {
			return "text/plain";
		}
	}

	/**
	 * Retorna a extenção do arquivo
	 * @return string - extenção do arquivo
	 */
	function getExt()
	{
		$arquivo = $this->path;
		$arrayArquivo = explode(".", $arquivo);
		return $arrayArquivo[count($arrayArquivo) - 1];
	}

	// ===== Funções auxiliares =================================================

	/**
	 * Função para ler o conteudo de um arquivo
	 *
	 * @return string - o conteudo do arquivo
	 */
	private function lerArquivo()
	{
		return file_get_contents($this->path);
	}

	/**
	 * Função para escrever no arquivo
	 *
	 * @param string - o conteudo que será escrito
	 * @return boolean
	 */
	function escreverArquivo(string $conteudo)
	{
		$arquivo = fopen($this->path, "w");
		fwrite($arquivo, $conteudo);
		fclose($arquivo);
		return true;
	}

	/**
	 * função para retornar os dados de um arquivo .json
	 *
	 * @return array - json decodificado
	 */
	function lerJson()
	{
		return json_decode($this->lerArquivo($this->path), true);
	}

	/**
	 * Função para escrever um arr em um arquivo .json
	 *
	 * @param array - array de dados que serão convertidos em json
	 * @return boolean
	 */
	function escreverJson(array $arr)
	{
		return $this->escreverArquivo(is_array($arr) ? json_encode($arr) : $arr);
	}
}
