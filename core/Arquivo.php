<?php

/**
 * Classe destinada a manipulação de arquivo
 */
class Arquivo
{
	private $path; //caminho até o arquivo (inclui o nome)
	private $ext; //extenção do arquivo (pegada altomaticamente)

	/**
	 * Função construtora do arquivo
	 *
	 * @param string - caminho até o arquivo
	 * @param boolean - caso o parametro seja true, um novo arquivo será criado
	 * @return boolean - valida se o arquivo existe
	 */
	public function __construct(string $arquivo, $novo = false)
	{
		if (empty($arquivo)) {
			return false;
		}

		if($novo){
			$arq = fopen($arquivo,'w');
			if($arq == false){
				return false;
			}else{
				fclose($arq);
			}
		}

		if (file_exists($arquivo)){
			$this->path = $arquivo;
			$arrayPath = explode(".", $this->path);
			$this->ext = $arrayPath[count($arrayPath) - 1];
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
		if(empty($this->path)){
			return false;
		}

		switch ($this->ext) {
			case 'json':
				return $this->lerJson();
				break;
			default:
				return $this->lerArquivo();
				break;
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
			case 'json':
				if(is_array($conteudo)){
					return $this->escreverJson($conteudo);
				}else{
					return false;
				}
				break;
			default:
				return $this->escreverArquivo($conteudo);
				break;
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
			case 'json':
				if(is_array($conteudo)){
					return $this->escreverJson(array_merge($this->ler(), $conteudo));
				}else{
					return false;
				}
				break;
			default:
				return $this->escreverArquivo($this->lerArquivo() . $conteudo);
				break;
		}
	}

	/**
	 * Função para ler o conteudo de um arquivo
	 *
	 * @return string - o conteudo do arquivo
	 */
	function lerArquivo()
	{
		// Cria o recurso (abrir o arquivo)
		$handle = fopen($this->path, "r");
		// Lê o arquivo
		$conteudo = fread($handle, filesize($this->path));
		// Fecha o arquivo
		fclose($handle);
		return $conteudo;
	}

	/**
	 * Função para escrever no arquivo
	 *
	 * @param string - o conteudo que será escrito
	 * @return boolean
	 */
	function escreverArquivo($conteudo)
	{
		//criamos o arquivo
		$arquivo = fopen($this->path, "w");
		//verificamos se foi criado
		if ($arquivo == false) {
			return false; //caso haja erro retorna o valor falso
		} else {
			//escrevemos no arquivo
			fwrite($arquivo, $conteudo);
			//Fechamos o arquivo após escrever nele
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
		return json_decode(file_get_contents($this->path), true);
	}

	/**
	 * Função para escrever um arr em um arquivo .json
	 *
	 * @param array - array de dados que serão convertidos em json
	 * @return boolean
	 */
	function escreverJson(array $arr)
	{
		if (is_array($arr)){
			return $this->escreverArquivo(json_encode($arr));
		}else{
			return false;
		}
	}
}
