<?php

class Banco extends Config
{
	public $conexao;
	public $tipo;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Efetua a conexão com o banco de dados
	 * A conexão sera salva na variavel $conexao e o tipo em $tipo
	 * @return array ["status" => boolean, "msg" => string]
	 */
	function conexao()
	{
		if (empty($this->conexao)) {
			try {
				$config = $this->getConfigBanco();
				$this->tipo = $config['tipo'];
				switch ($this->tipo) {
					case "mysql":
						$this->conexao = new PDO($config["stringConn"], $config["credenciais"]["login"], $config["credenciais"]["senha"]);
						break;
					case "sqlite":
					default:
						$caminhoArquivo = explode(":", $config["stringConn"])[1];
						new Arquivo($caminhoArquivo, !file_exists($caminhoArquivo));
						$this->conexao = new PDO($config["stringConn"]);
						break;
				}
			} catch (Exception $e) {
				return [
					"status" => false,
					"msg" => "Houve um erro ao se conectar com a base de dados " . ($this->debug ? $e : "")
				];
			}
		}
		return [
			"status" => true,
			"msg" => "Conectado a base de dados"
		];
	}

	/**
	 * Função para inserir dados no banco
	 * @param array $dados array associativo ['campo' => 'valor', 'campo2' => 'valor2', ...]
	 * @param string $tabela nome da tabela
	 * @param boolean $created - diz se a tabela contem os campos created e modified
	 * @return array ["status" => boolean, "msg"=> string] || retorno de $this->query
	 */
	public function insert(array $dados = [], string $tabela = '')
	{
		if (empty($dados)) {
			return [
				"status" => false,
				"msg" => "Não ha dados para inserir"
			];
		}
		if (empty($tabela)) {
			return [
				"status" => false,
				"msg" => "Nenhuma tabela definida para inserir os dados"
			];
		}

		//cria campos que são prenchidos altomaticamente pelo framework
		$campos = $this->query("DESC {$tabela}");
		foreach ($campos as $campo) {
			switch ($campo["Field"]) {
				case "criado":
				case "modificado":
					$dados[$campo["Field"]] = date("Y-m-d H:m:s");
					break;
			}
		}
		unset($campos);

		//transfomra o array associativo em script sql
		$campos = implode(", ", array_Keys($dados));
		$valores = "'" . implode("', '", $dados) . "'";
		$query = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

		return $this->query($query);
	}

	/**
	 * Função para pesquisar no banco de dados
	 * @param array array com os dados :
	 * indices do array:
	 * @param array campos - campos que serão listados ["campo1","campo2","campo3","campo4".....]
	 * @param string tabela - Nome da tabela
	 * @param string as - alias da tabela
	 * @param array join - joins da tabela ["join1","join2","join3","join4".....]
	 * @param array igual - campos que serão pesquisados ["campo" => "valor", "campo2" => "valor", "campo3" => "valor", ...]
	 * @param string where - String que será adicionada apos o where
	 * @param string order - String que será adicionada a pos o order
	 * @param boolean contar - se serão listados apenas a quantidade de registros
	 * @return array ["status" => boolean, "retorno" => array]
	 */
	public function select(array $arr = [])
	{
		$query = "SELECT ";

		if (isset($arr["campos"])) {
			foreach ($arr["campos"] as $campo) {
				if (isset($arr["as"]) && !isset($arr["join"])) {
					$query .= $arr["as"] . ".";
				}
				$query .= $campo;
				$query .= ", ";
			}
			$query = rtrim($query, ", ");
			$query .= " ";
		} else {
			if (isset($arr["as"])) {
				$query .= $arr["as"] . ".";
			}
			$query .= "* ";
		}

		if (isset($arr["tabela"])) {
			$query .= "FROM `" . $arr["tabela"] . "` ";
			if (isset($arr["as"])) {
				$query .= "AS " . $arr["as"] . " ";
			}
		} else {
			return [
				"status" => false,
			];
		}

		if (isset($arr["join"])) {
			foreach ($arr["join"] as $join) {
				$query .= $join . " ";
			}
		}

		if (isset($arr["igual"]) || isset($arr["where"])) {
			$query .= "WHERE " . (isset($arr["igual"]) ? $this->where($arr["igual"]) : "") . ($arr["where"] ?? "");
		}

		if (isset($arr["order"])) {
			$query .= " ORDER BY {$arr["order"]}";
		}

		$query = rtrim($query, " ");
		$query .= ";";

		return $this->query($query);
	}

	/**
	 * Função para atualizar os dados
	 * @param string $tabela
	 * @param array $dados - array associativo ["campo" => "valor", "campo" => "valor"]
	 * @param array|string $where - dados where
	 * @param boolean $campo created
	 * @return array ["status" => boolean, "msg" => "string"]
	 */
	public function update(string $tabela, array $dados, $where)
	{
		if (empty($dados)) {
			return [
				"status" => false,
				"msg" => "Não ha dados para atualizar"
			];
		}
		if (empty($tabela)) {
			return [
				"status" => false,
				"msg" => "Nenhuma tabela definida para atualizar"
			];
		}

		//cria campos que são prenchidos altomaticamente pelo framework
		$campos = $this->query("DESC {$tabela}");
		foreach ($campos as $campo) {
			switch ($campo["Field"]) {
				case "criado":
				case "modificado":
					$dados[$campo["Field"]] = date("Y-m-d H:m:s");
					break;
			}
		}
		unset($campos);

		$dadosWhere = $this->where($where);

		$camposValores = "";
		//seta a string dos dados
		foreach ($dados as $key => $value) {
			$camposValores .= "`{$key}` = '{$value}', ";
		}
		$camposValores = rtrim($camposValores, ", ");

		$query = "UPDATE {$tabela} SET {$camposValores} WHERE {$dadosWhere}";

		return $this->query($query);
	}

	/**
	 * Função para apagar registros da base de dados
	 * @param string $tabela - nome da tabela
	 * @param array|string $where - condições da query
	 * @return array ["status" => "boolean", ]
	 */
	public function delete(string $tabela, $where)
	{
		return $this->query("DELETE FROM {$tabela} WHERE {$this->where($where)}");
	}

	/**
	 * executa uma query sql
	 *
	 * @param string $query
	 * @return array ["status" => boolean, "retorno" => array]
	 */
	function query(string $query)
	{
		$conn = $this->conexao();
		if (!$conn["status"]) return ["status" => false];

		if ($this->tipo == "sqlite") $query = $this->sqlite($query);

		switch (strtoupper(explode(" ", $query)[0])) {
			case "SELECT":
				$execucao = $this->conexao->query($query);
				return [
					"status" => true,
					"retorno" => $execucao->fetchAll(PDO::FETCH_ASSOC)
				];
				break;
			case "INSERT":
				$execucao = $this->conexao->query($query);
				return [
					"status" => true,
					"retorno" => $this->conexao->lastInsertId()
				];
				break;
			case "SHOW":
				$execucao = $this->conexao->query($query);
				$retorno = [];
				foreach ($execucao->fetchAll(PDO::FETCH_ASSOC) as $tabela) {
					foreach ($tabela as $nome) {
						$retorno[] = $nome;
					}
				}
				return [
					"status" => true,
					"retorno" => $retorno
				];
				break;
			case "CREATE":
			case "UPDATE":
			case "DELETE":
			case "DROP":
			default:
				return [
					"status" => true,
					"retorno" => $this->conexao->query($query)
				];
				break;
		}
		return [
			"status" => false
		];
	}

	/**
	 * Função para transformar uma query mysql para sqlite
	 * @param string - query mysql
	 * @return string - query sqlite
	 */
	function sqlite(string $query)
	{
		$query = str_replace("AUTO_INCREMENT", "AUTOINCREMENT", $query);
		$query = str_replace("int(11)", "INTEGER", $query);
		return $query;
	}

	/**
	 * Função para montar a query where
	 * @param string|array $where - dados do where
	 * @return string query where
	 */
	private function where($where)
	{
		$dadosWhere = "";

		if (is_array($where)) {
			foreach ($where as $key => $val) {
				if (is_integer($val) || is_bool($val)) {
					$dadosWhere .= "{$key} = {$val} AND ";
				} else {
					$dadosWhere .= "{$key} = '{$val}' AND ";
				}
			}
			$dadosWhere = rtrim($dadosWhere, "AND ");
		} else if (is_string($where)) {
			$dadosWhere .= $where . " ";
		}

		return $dadosWhere;
	}
}
