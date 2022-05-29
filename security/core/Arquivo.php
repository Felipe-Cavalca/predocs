<?php

/**
 * Classe destinada a manipulação de arquivo
 */
class Arquivo
{
	public $path; //caminho até o arquivo (inclui o nome)
	public $ext; //extenção do arquivo
	public $mime; //mimetype do arquivo

	/**
	 * Função construtora do arquivo
	 *
	 * @param string - caminho até o arquivo
	 * @param bool - caso o parametro seja true, um novo arquivo será criado
	 * @param bool - Indica se o arquivo está no storage ou não
	 * @return bool - valida se o arquivo existe
	 */
	public function __construct(string $arquivo, bool $novo = false, bool $storage = true)
	{
		if($storage){
			$arquivo = "security/storage/files/".$arquivo;
		}

		//valida se o arquivo não é vazio
		if (empty($arquivo)) {
			return false;
		}

		//caso seja para criar um novo arquivo
		if ($novo) {
			//cria o diretorio caso não exista
			$arrayArquivo = explode("/", $arquivo);
			unset($arrayArquivo[count($arrayArquivo) - 1]);
			$dir = implode("/", $arrayArquivo);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}

			//cria o arquivo
			$arq = fopen($arquivo, 'w');
			//verifica se foi criado
			if ($arq == false) {
				return false;
			} else {
				fclose($arq);
			}
		}

		//caso o arquivo exista salva o caminho e a extenção
		if (file_exists($arquivo)) {
			$this->path = $arquivo;
			$this->ext = $this->getExt();
			$this->mime = $this->getMimeType();
			return true;
		}

		return false;
	}

	/**
	 * Função para pegar o conteudo de um arquivo
	 *
	 * @return array - caso seja um arquivo .json
	 * @return string - conteudo do arquivo
	 */
	public function ler()
	{
		switch ($this->ext) {
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
		switch ($this->ext) {
			case 'json': //caso json valida se o conteudo é um array e chama a função escreverJson
				if (is_array($conteudo)) {
					return $this->escreverJson($conteudo);
				} else {
					return false;
				}
			default: //função para escrever arquivo
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
		switch ($this->ext) {
			case 'json': //caso json faz um merge do ler com o que foi enviado
				if (is_array($conteudo)) {
					return $this->escreverJson(array_merge($this->ler(), $conteudo));
				} else {
					return false;
				}
			default:
				return $this->escreverArquivo($this->lerArquivo() . $conteudo);
		}
	}

	/**
	 * Função para ler e exibir o conteudo de um arquivo na tela
	 */
	public function renderiza()
	{
		switch ($this->ext) {
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
		//verificamos se foi criado
		if ($arquivo == false) {
			return false; //caso haja erro retorna o valor falso
		} else {
			//escrevemos no arquivo
			fwrite($arquivo, $conteudo);
			fclose($arquivo);
		}
		return true; //caso a função tenha sido executada com sucesso
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
		if (is_array($arr)) {
			return $this->escreverArquivo(json_encode($arr));
		} else {
			return false;
		}
	}

	/**
	 * Função para pegar o tempo de cache do arquivo
	 * @return string - cache a ser colocado no arquivo
	 */
	function tempoCache()
	{
		$config = new Config;
		$cache = $config->getconfigCache();

		//caso o cache esteja desativado, ou o arquivo estiver no excluir
		if (!$cache["ativo"] || isset($cache["excluir"][$this->ext])) {
			return "no-cache";
		}

		//caso seja para incluir
		if (isset($cache["incluir"][$this->ext])) {
			return "private, max-age=" . $cache['incluir'][$this->ext] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
		}

		//padrão
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
		if (!empty($arquivo) && file_exists($arquivo)) {
			switch ($this->getExt()) {
				case "js":
					return "application/javascript";
				case "css":
					return "text/css";
				default:
					return mime_content_type($arquivo);
					break;
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
}
