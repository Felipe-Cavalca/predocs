<?php

class Banco
{
    public $conexao;
    public $tipo;

    /**
     * Inicia a classe
     * @version 1
     * @access public
     * @return void
     * A conexão sera salva na variavel $conexao e o tipo em $tipo
     */
    public function __construct()
    {
        $this->conexao();
        return;
    }

    /**
     * Função para iniciar uma transação no banco de dados
     * @version 1
     * @access public
     * @return bool
     */
    public function inicio(): bool
    {
        return $this->conexao->beginTransaction();
    }

    /**
     * Função para realizar um roolback
     * @version 1
     * @access public
     * @return bool
     */
    public function reverter(): bool
    {
        return $this->conexao->rollback();
    }

    /**
     * Função para salvar os dados apos o inicio
     * @version 1.1
     * @access public
     * @return bool
     */
    public function salvar(): bool
    {
        if (isset($_SERVER["HTTP_TEST"])) {
            return true;
        }
        return $this->conexao->commit();
    }

    /**
     * Efetua a conexão com o banco de dados
     * @version 2.1.1
     * @access public
     * @return bool
     * A conexão sera salva na variavel $conexao e o tipo em $tipo
     */
    public function conexao(): bool
    {
        $config = new Config();
        $config = $config->getConfigBanco();

        if (!empty($GLOBALS["_BANCO"]["conexao"])) {
            $this->conexao = $GLOBALS["_BANCO"]["conexao"];
            $this->tipo = $GLOBALS["_BANCO"]["tipo"];
            return true;
        }

        $this->tipo = $config["tipo"];

        try {
            if ($this->tipo === "mysql") {
                $this->conexao = new PDO(
                    $config["stringConn"],
                    $config["credenciais"]["login"],
                    $config["credenciais"]["senha"]
                );
            } else {
                $caminhoArquivo = explode(":", $config["stringConn"])[1];
                new Arquivo(arquivo: $caminhoArquivo, novo: true);
                $this->conexao = new PDO($config["stringConn"]);
            }

            $GLOBALS["_BANCO"]["conexao"] = $this->conexao;
            $GLOBALS["_BANCO"]["tipo"] = $this->tipo;

            return true;
        } catch (Exception $e) {
            new Log("Erro ao se conectar a base de dados " . $e, "core/banco", "conexao");
            return false;
        }
    }

    /**
     * Função para inserir dados no banco
     * @version 1
     * @access public
     * @param array $dados array associativo ['campo' => 'valor', 'campo2' => 'valor2', ...]
     * @param string $tabela nome da tabela
     * @return bool|int
     */
    public function insert(array $dados, string $tabela): bool|int
    {
        $erros = [];
        if (empty($dados)) {
            $erros[] = "Sem dados para inserir";
        }
        if (empty($tabela)) {
            $erros[] = "Nenhuma tabela definida";
        }
        if (!$this->existeTabela($tabela)) {
            $erros[] = "Tabela {$tabela} inexistente";
        }
        if (!empty($erros)) {
            new Log(implode(", ", $erros), "core/banco", "insert");
            return false;
        }

        // Adiciona campos criados e modificados
        $campos = $this->detTabela(tabela: $tabela);
        foreach ($campos as $campo) {
            if ($campo["nome"] === "criado" || $campo["nome"] === "modificado") {
                $dados[$campo["nome"]] = date("Y-m-d H:m:s");
            }
        }

        // Gera o script SQL
        $campos = implode(", ", array_keys($dados));
        $valores = "'" . implode("', '", $dados) . "'";
        $query = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

        $retorno = $this->query(query: $query);
        $this->logTabela($tabela, "INSERT", json_encode(["dados" => $dados]));

        if ($retorno === false) {
            new Log("Não foi possível inserir os dados na base de dados", "core/banco", "insert");
            return false;
        }

        return $retorno;
    }

    /**
     * Função para pesquisar no banco de dados
     * @version 1.1
     * @access public
     * @param array arr array com os dados :
     * indices do array:
     * @param array campos campos que serão listados ["campo1","campo2","campo3","campo4".....]
     * @param string tabela Nome da tabela
     * @param string as alias da tabela
     * @param array join joins da tabela ["join1","join2","join3","join4".....]
     * @param array igual campos que serão pesquisados ["campo" => "valor", "campo2" => "valor", "campo3" => "valor", ...]
     * @param string where String que será adicionada apos o where
     * @param string order String que será adicionada a pos o order
     * @return array|false
     */
    public function select(array $arr = []): array|false
    {
        $funcoes = new funcoes();

        if ($funcoes->empty(["tabela"], $arr)["status"]) {
            new Log("Tabela não definida para listar", "core/banco", "select");
            return false;
        }

        if (!$this->existeTabela($arr["tabela"])) {
            new Log("Tabela {$arr["tabela"]} não localizada para listar", "core/banco", "select");
            return false;
        }

        $where = [];
        if (isset($arr["igual"]))
            $where[] = $this->where($arr["igual"]);
        if (isset($arr["where"]))
            $where[] = $this->where($arr["where"]);
        if (empty($where)) {
            $where = "";
        } else {
            $where = "WHERE " . implode(" ", $where);
        }

        $order = "";
        if (isset($arr["order"])) {
            $order .= " ORDER BY {$arr["order"]}";
        }

        $selectHelper = new SelectHelper();

        $query = [
            "SELECT",
            $selectHelper->campos($arr),
            "FROM",
            $selectHelper->tabela($arr),
            $selectHelper->join($arr),
            $where,
            $order
        ];

        return $this->query(query: implode(" ", $query));
    }

    /**
     * Função para atualizar os dados
     * @version 1
     * @access public
     * @param string $tabela
     * @param array $dados array associativo ["campo" => "valor", "campo" => "valor"]
     * @param array|string $where dados where
     * @return bool
     */
    public function update(string $tabela, array $dados, $where): bool
    {
        if (empty($dados)) {
            new Log("Sem dados para atualizar", "core/banco", "update");
            return false;
        }
        if (empty($tabela)) {
            new Log("Nenhuma tabela definida para atualizar os dados", "core/banco", "update");
            return false;
        }
        if (!$this->existeTabela($tabela)) {
            new Log("Tabela {$tabela} inexistente", "core/banco", "update");
            return false;
        }

        //cria campos que são prenchidos pelo framework
        $campos = $this->detTabela($tabela);
        foreach ($campos as $campo) {
            switch ($campo["nome"]) {
                case "modificado":
                    $dados[$campo["nome"]] = date("Y-m-d H:m:s");
                    break;
            }
        }

        $camposValores = [];
        foreach ($dados as $key => $value) {
            $camposValores[] = "`{$key}` = '{$value}'";
        }
        $camposValores = implode(", ", $camposValores);

        $dadosWhere = $this->where($where);

        $query = "UPDATE {$tabela} SET {$camposValores} WHERE {$dadosWhere}";

        $this->logTabela($tabela, "UPDATE", json_encode(["dados" => $dados, "where" => $where]));

        return $this->query($query);
    }

    /**
     * Função para apagar registros da base de dados
     * @version 1
     * @access public
     * @param string $tabela nome da tabela
     * @param array|string $where condições da query
     * @return bool
     */
    public function delete(string $tabela, $where): bool
    {
        if (empty($tabela)) {
            new log("Nenhuma tabela definida para deletar dados", "core/banco", "delete");
            return false;
        }

        if (!$this->existeTabela(tabela: $tabela)) {
            new Log("Tabela {$tabela} inexistente", "core/banco", "delete");
            return false;
        }

        $query = "DELETE FROM {$tabela} WHERE {$this->where($where)}";

        if ($this->query(query: $query) === false) {
            new Log("Erro ao executar script de delete", "core/banco", "delete");
            return false;
        }

        $this->logTabela($tabela, "DELETE", json_encode(["where" => $where]));

        return true;
    }

    /**
     * executa uma query sql
     * @version 1.1
     * @access public
     * @param string $query
     * @return bool|array|int|PDOStatement
     * bool sucesso ou erro
     * array dados
     * int id
     * PDOStatement objeto pdo
     */
    public function query(string $query): bool|array|int|PDOStatement
    {
        if (!$this->conexao())
            return false;

        if ($this->tipo == "sqlite") $query = $this->sqlite($query);

        switch (strtoupper(explode(" ", $query)[0])) {
            case "SELECT":
                $execucao = $this->conexao->query($query);
                return $execucao->fetchAll(PDO::FETCH_ASSOC);
                break;
            case "INSERT":
                $this->conexao->query($query);
                return $this->conexao->lastInsertId();
                break;
            case "SHOW": //lista tabelas do banco
            case "DESC": //comando mysql para dados da tabela
            case "PRAGMA": //comando sqlite para dados da tabela
                $execucao = $this->conexao->query($query);
                return $execucao->fetchAll(PDO::FETCH_ASSOC);
                break;
            case "UPDATE":
                if ($this->conexao->query($query) === false)
                    return false;
                return true;
                break;
            case "CREATE":
            case "DELETE":
            case "DROP":
            default:
                return $this->conexao->query($query);
                break;
        }

        return false;
    }

    /**
     * Função para transformar uma query mysql para sqlite
     * @version 1
     * @access public
     * @param string query mysql
     * @return string query sqlite
     */
    function sqlite(string $query): string
    {
        $query = str_replace("AUTO_INCREMENT", "AUTOINCREMENT", $query);
        $query = preg_replace("/int(\([a-zA-Z0-9]{1,}\))/", "INTEGER", $query);
        switch (strtoupper(explode(" ", $query)[0])) {
            case "DESC":
                $query = str_replace("DESC ", "PRAGMA table_info(", $query) . ");";
                break;
        }
        return $query;
    }

    /**
     * Função para montar a query where
     * @version 1
     * @access private
     * @param string|array $where dados do where
     * @return string query where
     */
    private function where(string|array $where): string
    {
        $dadosWhere = [];

        if (is_array($where)) {
            foreach ($where as $key => $val) {
                if (is_integer($val) || is_bool($val))
                    $dadosWhere[] = "{$key} = {$val}";
                else
                    $dadosWhere[] = "{$key} = '{$val}'";
            }
            return implode(" AND ", $dadosWhere);
        } else if (is_string($where)) {
            return $where;
        }
    }

    /**
     * Função para listar os campos de uma tabela
     * @version 1
     * @access public
     * @param string $tabela nome da tabela
     * @return array detalhes dos camps da tabela
     */
    public function detTabela(string $tabela): array
    {
        if (!in_array($tabela, $this->getTabelas()))
            return [];

        if ($this->tipo == "mysql") {
            $campos = [];
            foreach ($this->query(query: "DESC {$tabela}") as $campo) {
                $campos[] = [
                    "nome" => $campo["Field"],
                    "tipo" => $campo["Type"],
                    "null" => $campo["Null"] == "YES",
                    "padrao" => $campo["Default"],
                    "pk" => $campo["Extra"] == "auto_increment"
                ];
            }
            return $campos;
        } else if ($this->tipo == "sqlite") {
            $campos = [];
            foreach ($this->query(query: "PRAGMA table_info ('{$tabela}')") as $campo) {
                if ($campo["type"] == "INTEGER")
                    $campo["type"] = "int(11)";
                $campos[] = [
                    "nome" => $campo["name"],
                    "tipo" => $campo["type"],
                    "null" => !$campo["notnull"],
                    "padrao" => $campo["dflt_value"],
                    "pk" => $campo["pk"] == 1
                ];
            }
            return $campos;
        } else {
            return [];
        }
    }

    /**
     * Função para listar as tabelas do banco
     * @version 1
     * @access public
     * @return array array com o nome das tabelas
     */
    public function getTabelas(): array
    {
        if ($this->tipo == "mysql") {
            $retorno = [];
            foreach ($this->query(query: "SHOW TABLES") as $tabela) {
                foreach ($tabela as $nome) {
                    $retorno[] = $nome;
                }
            }
            return $retorno;
        } else if ($this->tipo == "sqlite") {
            $retorno = [];
            foreach ($this->query(query: "SELECT * FROM sqlite_master WHERE type='table'") as $tabela) {
                if ($tabela["name"] != "sqlite_sequence")
                    $retorno[] = $tabela["name"];
            }
            return $retorno;
        } else {
            return [];
        }
    }

    /**
     * Valida se uma tabela existe
     * @version 1
     * @access public
     * @return bool
     */
    public function existeTabela(string $tabela): bool
    {
        return in_array($tabela, $this->getTabelas());
    }

    /**
     * Insere o log em sua tabela de log
     * @version 1
     * @access private
     * @return bool
     */
    private function logTabela(string $tabela, string $acao, string $query): bool
    {
        //valida se não é a tabela de log
        if (substr($tabela, -4) == "_log") {
            return true;
        }

        if (!$this->existeTabela($tabela . "_log")) {
            $queryTabelaLog = "CREATE TABLE IF NOT EXISTS {$tabela}_log (
				`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`acao` varchar(255) NOT NULL,
				`query` LONGTEXT,
				`ip` varchar(255),
				`criado` datetime
			);";

            $this->query($queryTabelaLog);
        }

        // Valida se existe o ip do usuario
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        $this->insert([
            "acao" => $acao,
            "query" => $query,
            "ip" => $_SERVER['REMOTE_ADDR']
        ], $tabela . "_log");

        return true;
    }
}

/**
 * Classe para auxiliar nas funções do banco
 */
class SelectHelper
{
    /**
     * função para retornar os campos da query
     * @version 1.1
     * @access public
     * @param array $arr array do select
     * @return string alias utilizado em fields
     */
    public function campos(array $arr): string
    {
        $as = $this->alias($arr);

        if (isset($arr["join"]))
            $as = null;

        if (!empty($as))
            $as = "$as.";

        //define os campos que serão listados
        $campos = [];
        if (isset($arr["campos"]) && is_array($arr["campos"])) {
            foreach ($arr["campos"] as $campo) {
                $campos[] = "{$as}{$campo}";
            }
        } else {
            $campos[] = "$as*";
        }

        return implode(", ", $campos);
    }

    /**
     * Função para pegar o nome da tabela no select
     * @version 1.1
     * @access public
     * @param array $arr array do select
     * @return string
     */
    public function tabela($arr): string
    {
        $as = $this->alias($arr);

        return "{$arr["tabela"]} {$as}";
    }

    /**
     * Função para pegar o alias da query
     * @version 1.1
     * @access public
     * @param array $arr array do select
     * @return string
     */
    public function alias($arr): string
    {
        //verifica o alias
        if (isset($arr["as"]))
            return $arr["as"];

        return "";
    }

    /**
     * Função para montas as strings de join
     * @version 1
     * @access public
     * @param array $array do select
     * @return string
     */
    public function join($arr): string
    {
        $retorno = [];
        if (isset($arr["join"])) {
            foreach ($arr["join"] as $join) {
                $retorno[] = $join;
            }
        }

        return implode(" ", $retorno);
    }
}
