<?php

/**
 * Classe destinada a manipulação de arquivo
 */
class Arquivo
{
	public $path; //caminho até o arquivo (inclui o nome)
	public $existe; //Diz se um arquivo existe ou não

	/**
	 * Função construtora do arquivo
	 *
	 * @param string - caminho até o arquivo
	 * @param bool - caso o parametro seja true, um novo arquivo será criado.
	 */
	public function __construct(string $arquivo, bool $novo = false)
	{
		$this->path = $arquivo;

		if ($novo) {
			$this->criar();
		}
	}

	/**
	 * Função para validadar se o arquivo existe
	 * @return bool - diz se o arquivo existe ou não
	 */
	public function existe()
	{
		return file_exists($this->path);
	}

	/**
	 * Função para criar o arquivo caso não exista
	 * @return int|bool - sucesso ou erro ao criar o arquivo
	 * Arquivo criado - int (numero de bytes do arquivo)
	 * Erro - bool - false - erro ao criar
	 */
	public function criar()
	{
		if ($this->existe()) {
			return $this->tamanho();
		}
		$this->criaPasta();
		return file_put_contents($this->path, "");
	}

	/**
	 * Função retorna o tamanho do arquivo em bytes
	 * @return int - tamanho do arquivo em bytes
	 */
	public function tamanho()
	{
		if ($this->existe()) {
			return filesize($this->path);
		}
		return 0;
	}

	/**
	 * Função para realizar o upload de um arquivo
	 *
	 * @param string $arquivo - arquivo original que será enviado
	 * @return bool - Caso o arquivo tenha sigo feito o upload
	 */
	public function upload(string $arquivo)
	{
		$atual = $this->path;
		$this->path = $arquivo;
		$this->criaPasta();
		move_uploaded_file($arquivo, $atual);
		$this->path = $atual;
	}

	/**
	 * @param string $destino - destino do arquivo
	 * @return bool - retorno caso o arquivo foi movido ou não
	 */
	public function mover(string $destino)
	{
		$atual = $this->path;
		$this->path = $destino;
		$this->criaPasta();
		if (rename($atual, $destino)) {
			return true;
		} else {
			$this->path = $atual;
		}
		return false;
	}

	/**
	 * @param string $destino - Destino do arquivo
	 */
	public function copiar(string $destino)
	{
		$atual = $this->path;
		$this->path = $destino;
		$this->criaPasta();
		$this->path = $atual;
		return copy($this->path, $destino);
	}

	/**
	 * Função para pegar o conteudo de um arquivo
	 *
	 * @return array|string - arrays para json, string para diversos
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
	 * @param string|array Array para arquivos json, string para outros tipos de arquivos
	 * @return bool
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
	 * @param string|array - Array para arquivos json, string para outros tipos de arquivos
	 * @return bool
	 */
	public function adicionar($conteudo)
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->escrever(array_merge($this->ler(), $conteudo));
			default:
				return $this->escrever($this->ler() . $conteudo);
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
	 * @return bool - diz se o arquivo foi apagado ou não
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
		$array = explode(".", $this->path);
		return end($array);
	}

	/**
	 * Retorna o nome do arquivo
	 * @return string - nome do arquivo
	 */
	function getNome()
	{
		$array = explode("/", $this->path);
		return end($array);
	}

	/**
	 * string com pastas até o arquivo
	 * @param bool - true para receber o retorno como array, default false
	 * @return string|array - caminho até o arquivo
	 */
	function getPasta(bool $array = false)
	{
		$retorno = str_replace($this->getNome(), "", $this->path);
		if ($array) {
			$retorno = explode("/", $retorno);
		}
		return $retorno;
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
	 * @return bool
	 */
	function escreverArquivo(string $conteudo)
	{
		return file_put_contents($this->path, $conteudo);
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
	 * @return bool
	 */
	function escreverJson(array $arr)
	{
		return $this->escreverArquivo(is_array($arr) ? json_encode($arr) : $arr);
	}

	/**
	 * Função para criar as pastas até o arquivo
	 * @return bool
	 */
	function criaPasta()
	{
		$pastas = $this->getPasta();
		if (!is_dir($pastas)) {
			mkdir($pastas, 0777, true);
		}
	}
}
