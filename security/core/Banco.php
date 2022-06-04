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
	 *
	 * @return pdo com a conexão com o banco
	 */
	function conexao()
	{
		if (empty($this->conexao)) {
			$config = $this->getConfigBanco();

			try {
				$this->tipo = $config['tipo'];
				switch ($this->tipo) {
					case "sqlite":
						$this->conexao = new PDO($config["stringConn"]);
						break;
					case "mysql":
					default:
						$this->conexao = new PDO($config["stringConn"], $config["credenciais"]["login"], $config["credenciais"]["senha"]);
						break;
				}
			} catch (Exception $e) {
				$retorno = [
					"status" => false,
					"msg" => "Houve um erro ao se conectar com a base de dados"
				];

				if ($this->debug) {
					$retorno['exception'] = $e;
				}

				return $retorno;
			}
		}
		return $this->conexao;
	}

	/**
	 * Função para inserir no banco
	 * não é necessario fazer a conexão, a função já faz
	 *
	 * @param array $dados array associativo ['campo' => 'valor', 'campo2' => 'valor2', ...]
	 * @param string $tabela nome da tabela
	 * @param boolean $created - diz se a tabela contem os campos created e modified
	 * @return array
	 */
	public function insert($dados = [], $tabela = '', $created = true)
	{
		try {
			//verifica se os dados ou a tabela estão vazios
			if (empty($dados)) {
				throw new Exception("Não ha dados para inserir");
			}
			if (empty($tabela)) {
				throw new Exception("Nenhuma tabela definida para inserir os dados");
			}

			if ($created) {
				//cria os campos de criado e modificado
				$dados["created"] = date("Y-m-d H:m:s");
				$dados["modified"] = date("Y-m-d H:m:s");
			}

			//transfomra o array associativo em script sql
			$campos = implode(", ", array_Keys($dados));
			$valores = ":" . implode(", :", array_keys($dados));
			$Create = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

			//faz a conexao
			$pdo = $this->conexao();

			//verifica se a conexão foi feita
			if (!$pdo) {
				throw new Exception("A conexão não foi estabelecida");
			}

			//prepara o script
			$sth = $pdo->prepare($Create);

			//faz o insert
			if ($sth->execute($dados)) {
				return ["status" => true, "id" => $pdo->lastInsertId()];
			} else {
				throw new Exception("O dado não foi inserido");
			}
		} catch (Exception $e) {
			$retorno = [
				"status" => false,
				"msg" => "Houve um erro na base de dados"
			];

			if ($this->debug) {
				$retorno['exception'] = $e;
			}

			return $retorno;
		}
	}

	/**
	 * Função para pesquisar no banco de dados
	 * indices do array:
	 * tabela - qual tabela será pesquisada
	 * campos - os campos que são pesquisados
	 * igual array - pesquisa os iguais ['indice' => 'valor', .....]
	 * igual string - coloca a string apos o where da query
	 * contar - true para contar a quantidade de registros na tabela
	 *
	 * @param array $arr
	 * @return array
	 */
	public function select($arr = [])
	{
		try {
			$query = "SELECT ";

			if (isset($arr["campos"])) {
				foreach ($arr["campos"] as $campo) {
					$query .= "`" . $campo . "`, ";
				}
				$query = rtrim($query, ", ");
				$query .= " ";
			} else {
				$query .= "* ";
			}

			if (isset($arr["tabela"])) {
				$query .= "FROM `" . $arr["tabela"] . "` ";
			} else {
				throw new Exception("Nenhuma tabela definida para a seleção");
			}

			if (isset($arr["igual"])) {
				$query .= "WHERE ";

				if (is_array($arr["igual"])) {
					foreach ($arr["igual"] as $campo => $valor) {
						$query .= "`" . $campo . "` = '" . $valor . "' AND ";
					}
					$query = rtrim($query, " AND");
				} else {
					$query .= $arr["igual"];
				}
			}

			$query = rtrim($query, " ");

			$query .= ";";

			$conn = $this->conexao();
			if (!$conn) {
				throw new Exception("A conexão não foi estabelecida");
			}

			$execucao = $conn->query($query);

			if (isset($arr["contar"]) && $arr["contar"]) {
				$execucao->execute();
				$retorno = $execucao->rowCount();
			} else {
				$retorno = $execucao->fetchAll(PDO::FETCH_ASSOC);
			}

			return ["status" => true, "retorno" => $retorno];
		} catch (Exception $e) {
			$retorno = [
				"status" => false,
				"msg" => "Houve um erro na base de dados"
			];

			if ($this->debug) {
				$retorno['exception'] = $e;
			}

			return $retorno;
		}
	}

	/**
	 * executa uma query sql
	 *
	 * @param string $query
	 * @return array
	 * status - scuesso na query ou não
	 * retorno - retorno da query
	 */
	function query($query, $select = null)
	{
		try {
			$conn = $this->conexao();
			if (!$conn) {
				throw new Exception('A conexão não foi estabelecida');
			}

			if ($this->tipo == "sqlite") {
				$query = str_replace("AUTO_INCREMENT", "", $query);
				$query = str_replace("enum", "varchar", $query);
			}

			//caso a função seja um select ela retira os indices numericos e mantem somente o nome da coluna
			if ($select) {
				$execucao = $conn->query($query);
				if (isset($arr["contar"]) && $arr["contar"]) {
					$execucao->execute();
					$retorno = $execucao->rowCount();
				} else {
					$retorno = $execucao->fetchAll(PDO::FETCH_ASSOC);
				}
			} else {
				$execucao = $conn->prepare($query);
				$execucao->execute();
				$retorno = [];
				foreach ($execucao as $res) {
					$retorno[] = $res;
				}
			}

			return ['status' => true, 'retorno' => $retorno];
		} catch (Exception $e) {
			$retorno = [
				"status" => false,
				"msg" => "Houve um erro na base de dados"
			];

			if ($this->debug) {
				$retorno['exception'] = $e;
			}

			return $retorno;
		}
	}
}
