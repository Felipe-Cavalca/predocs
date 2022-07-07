<?php

/**
 * Função para listar os arquivos da pasta sql e executa-los
 *
 * @return array - array com o stts e a mensagem
 */
function install()
{
	$config = new Config();
	$configBanco = $config->getConfigBanco();

	if(!$configBanco["instalado"]){
		if(!executeSqlPasta("security/sql/database/")){
			return [
				"status" => false,
				"msg" => "erro na instalação do bd"
			];
		}

		if (!executeSqlPasta("security/sql/data/")) {
			return [
				"status" => false,
				"msg" => "erro ao inserir dados"
			];
		}

		$configBanco["instalado"] = true;
		$config->setConfigBanco($configBanco);
	}

	return [
		"status" => true,
		"msg" => "Banco instalado com sucesso"
	];
}

function update(){
	if (!executeSqlPasta("security/sql/update/", true)) {
		return [
			"status" => false,
			"msg" => "erro ao atualizar"
		];
	}

	return [
		"status" => true,
		"msg" => "Banco atualizado com sucesso"
	];
}

function executeSqlPasta($pasta, $apagar = false){
	$banco = new Banco();
	$arquivos = listarArquivos($pasta);

	foreach ($arquivos as $sql) {
		if ($sql == '.' || $sql == '..' || $sql == 'database.txt' || $sql == 'data.txt' || $sql == 'update.txt') {
			continue;
		}

		$arquivo = new Arquivo($pasta . $sql);

		if ($banco->query($arquivo->ler())['status']) {
			if($apagar){
				$arquivo->apagar();
			}
			continue;
		} else {
			return false;
		}
	}

	return true;
}
