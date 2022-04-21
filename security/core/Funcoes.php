<?php

class Funcoes
{
	/**
	 * Função para printar algo na tela
	 *
	 * @param any $data
	 * @return string
	 */
	function pr($data)
	{
		echo '<pre>' . print_r($data, true) . '</pre>';
	}

	/**
	 * Função para listar os arquivos de uma pasta
	 *
	 * @param string $path - caminho da lista de pastas
	 * @return array - arry com os nomes dos arquivos/pastas de dentro do diretorio
	 */
	public function listar($path = '/')
	{
		$diretorio = dir($path);

		$arquivos = [];
		while ($arquivo = $diretorio->read()) {
			$arquivos[] = $arquivo;
		}
		$diretorio->close();

		return $arquivos;
	}

	/**
	 * Retorna o mimetype do arquivo
	 *
	 * @return string - mimetype do arquivo
	 */
	function getMimeType(string $arquivo)
	{
		switch ($this->getExt($arquivo)) {
			case "js":
				return "application/javascript";
			case "css":
				return "text/css";
			default:
				return mime_content_type($this->path);
				break;
		}
	}

	/**
	 * Retorna a extenção do arquivo
	 * @param string - caminho até o arquivo
	 * @return string - extenção do arquivo
	 */
	function getExt(string $arquivo)
	{
		$arrayPath = explode(".", $arquivo);
		return $arrayPath[count($arrayPath) - 1];
	}
}
