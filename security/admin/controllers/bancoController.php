<?php

/**
 * Função para listar os arquivos da pasta sql e executa-los
 *
 * @return array - array com o stts e a mensagem
 */
function install()
{
	$pasta  = "security/sql/";
	$banco = new Banco();
	$arquivos = listarArquivos($pasta);

	foreach ($arquivos as $sql) {
		if ($sql == '.' || $sql == '..') {
			continue;
		}

		$arquivo = new Arquivo($pasta . $sql);

		if ($banco->query($arquivo->ler())['status']) {
			continue;
		} else {
			return [
				"status" => false,
				"msg" => "erro na instalação do bd"
			];
		}
	}

	return [
		"status" => true,
		"msg" => "Banco instalado com sucesso"
	];
}
