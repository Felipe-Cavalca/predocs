<?php

/**
 * Classe destinada a manipulação de arquivo
 * @version 1
 * @access public
 * @param string $arquivo - caminho até o arquivo
 * @param bool $novo
 */
class Arquivo
{
	/**
	 * Caminho até o arquivo.
	 * @access private
	 * @type string
	 */
	private $path; //caminho até o arquivo (inclui o nome)

	/**
	 * Função construtora do arquivo
	 *
	 * @version 1
	 * @access public
	 * @param string $arquivo caminho até o arquivo
	 * @param bool $novo
	 * @return void
	 */
	public function __construct(string $arquivo, bool $novo = false)
	{
		$this->path = $arquivo;
		if ($novo) $this->criar();
		return;
	}

	/**
	 * Função para validadar se o arquivo existe
	 * @version 1
	 * @access public
	 * @return bool
	 */
	public function existe(): bool
	{
		return file_exists(filename: $this->path);
	}

	/**
	 * Função para criar o arquivo caso não exista
	 * @version 1
	 * @access public
	 * @return int|bool
	 * int - Arquivo criado
	 * bool - erro ao criar arquivo
	 */
	public function criar(): int|bool
	{
		if ($this->existe()) return $this->tamanho();
		$this->criaPasta();
		return file_put_contents(filename: $this->path, data: "");
	}

	/**
	 * Função retorna o tamanho do arquivo em bytes
	 * @version 1
	 * @access public
	 * @return int
	 * tamanho do arquivo em bytes
	 */
	public function tamanho(): int
	{
		if ($this->existe()) return filesize(filename: $this->path);
		return 0;
	}

	/**
	 * Função para realizar o upload de um arquivo
	 * @version 1
	 * @access public
	 * @param string $arquivo arquivo original quer será enviador
	 * @return bool
	 */
	public function upload(string $arquivo): bool
	{
		$atual = $this->path;
		$this->path = $arquivo;
		$this->criaPasta();
		if (move_uploaded_file(from: $arquivo, to: $atual)) {
			$this->path = $atual;
			return true;
		}
		return false;
	}

	/**
	 * Mover arquivo de pasta
	 * @version 1
	 * @access public
	 * @param string $destino destino do arquivo
	 * @return bool
	 */
	public function mover(string $destino): bool
	{
		$atual = $this->path;
		$this->path = $destino;
		$this->criaPasta();
		if (rename(from: $atual, to: $destino)) return true;
		else
			$this->path = $atual;
		return false;
	}

	/**
	 * Copiar arquivo atual para outro diretorio
	 * @version 1
	 * @access public
	 * @param string $destino Destino do arquivo
	 * @return bool
	 */
	public function copiar(string $destino): bool
	{
		$atual = $this->path;
		$this->path = $destino;
		$this->criaPasta();
		$this->path = $atual;
		return copy(from: $this->path, to: $destino);
	}

	/**
	 * Função para ler o arquivo
	 * @version 1
	 * @access public
	 * @return array|string
	 * array caso arquivos json
	 * string para outros
	 */
	public function ler(): array|string
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->lerJson();
			default:
				return $this->lerArquivo();
		}
	}

	/**
	 * Função para escrever no arquivo
	 * @version 1
	 * @access public
	 * @param string|array $conteudo conteudo do arquivo
	 * @return bool
	 */
	public function escrever(string|array $conteudo): bool
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->escreverJson(arr: $conteudo);
			default:
				return $this->escreverArquivo(conteudo: $conteudo);
		}
	}

	/**
	 * adiciona conteudo ao final do arquivo
	 * @version 1
	 * @access public
	 * @param string|array $conteudo conteudo que será escrito
	 * @return bool
	 */
	public function adicionar(string|array $conteudo): bool
	{
		switch ($this->getExt()) {
			case 'json':
				return $this->escrever(conteudo: array_merge($this->ler(), $conteudo));
			default:
				return $this->escrever(conteudo: $this->ler() . $conteudo);
		}
		return false;
	}

	/**
	 * Função para ler e exibir o conteudo de um arquivo na tela
	 * @version 1
	 * @access public
	 * @return void
	 * Retorna void, porem renderiza o arquivo na tela/request
	 */
	public function renderiza(): void
	{
		switch ($this->getExt()) {
			case "php":
				include_once($this->path);
				break;
			default:
				header("Content-Type: " . $this->getMimeType());
				header("Cache-Control: " . $this->tempoCache());
				readfile(filename: $this->path);
				break;
		}
		return;
	}

	/**
	 * Função para apagar um arquivo
	 * @version 1
	 * @access public
	 * @return bool diz se o arquivo foi apagado ou não
	 */
	public function apagar(): bool
	{
		return unlink(filename: $this->path);
	}

	/**
	 * Função para pegar o tempo de cache do arquivo
	 * @version 1
	 * @access public
	 * @return string cache a ser colocado no arquivo
	 */
	public function tempoCache(): string
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
	 * @version 1
	 * @access public
	 * @return string mimetype do arquivo
	 */
	public function getMimeType(): string
	{
		$arquivo = $this->path;
		if ($this->existe()) {
			switch ($this->getExt()) {
				case "js":
					return "application/javascript";
				case "css":
					return "text/css";
				default:
					return mime_content_type(filename: $arquivo);
			}
		} else {
			return "text/plain";
		}
	}

	/**
	 * Retorna a extenção do arquivo
	 * @version 1
	 * @access public
	 * @return string extenção do arquivo
	 */
	public function getExt(): string
	{
		$array = explode(".", $this->path);
		return end($array);
	}

	/**
	 * Retorna o nome do arquivo
	 * @version 1
	 * @access public
	 * @return string nome do arquivo
	 */
	public function getNome(): string
	{
		$array = explode("/", $this->path);
		return end($array);
	}

	/**
	 * string com pastas até o arquivo
	 * @version 1
	 * @access public
	 * @param bool $array true para receber o retorno como array, default false
	 * @return string|array - caminho até o arquivo
	 */
	public function getPasta(bool $array = false): string|array
	{
		$retorno = str_replace(search: $this->getNome(), replace: "", subject: $this->path);
		if ($array) {
			$retorno = explode(separator: "/", string: $retorno);
		}
		return $retorno;
	}

	// ===== Funções auxiliares =================================================

	/**
	 * Função para ler o conteudo de um arquivo
	 * @version 1
	 * @access private
	 * @return string o conteudo do arquivo
	 */
	private function lerArquivo(): string
	{
		return file_get_contents(filename: $this->path);
	}

	/**
	 * Função para escrever no arquivo
	 * @version 1
	 * @access private
	 * @param string o conteudo que será escrito
	 * @return bool
	 */
	private function escreverArquivo(string $conteudo): bool
	{
		return file_put_contents(filename: $this->path, data: $conteudo);
	}

	/**
	 * função para retornar os dados de um arquivo .json
	 * @version 1
	 * @access private
	 * @return array json decodificado
	 */
	private function lerJson(): array
	{
		return json_decode($this->lerArquivo(), true);
	}

	/**
	 * Função para escrever um arr em um arquivo .json
	 * @version 1
	 * @access private
	 * @param array $arr de dados que serão convertidos em json
	 * @return bool
	 */
	private function escreverJson(array $arr): bool
	{
		return $this->escreverArquivo(conteudo: is_array($arr) ? json_encode($arr) : $arr);
	}

	/**
	 * Função para criar as pastas até o arquivo
	 * @version 1
	 * @access private
	 * @return void
	 */
	private function criaPasta(): void
	{
		$pastas = $this->getPasta();
		if (!is_dir($pastas)) mkdir($pastas, 0777, true);
		return;
	}
}
