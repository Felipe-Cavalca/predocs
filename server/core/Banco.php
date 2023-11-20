<?php

class Banco
{
    public $conexao;
    public $tipo;
    private  $nomeArquivo = "core/banco";
    private static $instance;

    /**
     * Construtor privado da classe.
     * Inicializa a conexão no momento da criação do objeto.
     * @return void
     */
    public function __construct()
    {
        $this->conexao();
    }

    /**
     * Retorna uma instância única da classe, seguindo o padrão Singleton.
     * Se a instância ainda não existir, cria uma nova e a retorna.
     * @return self Instância única da classe.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Inicia uma transação no banco de dados, se aplicável.
     *
     * Esta função verifica se uma conexão está ativa e se uma transação já não está em andamento.
     * Se uma conexão existe e não há transação ativa, inicia uma nova transação.
     *
     * @return bool Retorna verdadeiro se uma nova transação foi iniciada com sucesso ou falso se já havia uma transação em andamento ou se não há conexão.
     */
    public function inicio(): bool
    {
        if ($this->conexao instanceof PDO) {
            // Verifica se não há uma transação em andamento
            if (!$this->conexao->inTransaction()) {
                return $this->conexao->beginTransaction(); // Inicia uma nova transação
            } else {
                // Já existe uma transação em andamento.
                return false;
            }
        } else {
            // Não há conexão ativa.
            return false;
        }
    }

    /**
     * Reverte uma transação pendente no banco de dados, se existir.
     *
     * Verifica se existe uma conexão ativa e se uma transação está em andamento.
     * Se ambos os critérios forem atendidos, reverte a transação atual no banco de dados.
     *
     * @return bool Retorna verdadeiro se a transação foi revertida com sucesso ou falso se não há transação em andamento ou se não há conexão ativa.
     */
    public function reverter(): bool
    {
        if ($this->conexao instanceof PDO && $this->conexao->inTransaction()) {
            return $this->conexao->rollback(); // Reverte a transação atual no banco de dados
        } else {
            // Não há conexão ativa ou não há transação em andamento.
            return false;
        }
    }

    /**
     * Salva os dados após o início de uma transação no banco de dados, se aplicável.
     *
     * Esta função é responsável por salvar os dados no banco de dados depois de iniciar uma transação, se uma conexão estiver ativa e uma transação estiver em andamento.
     * Ela verifica se a variável de ambiente HTTP_TEST está definida; se sim, retorna verdadeiro.
     * Caso contrário, se uma conexão com o banco de dados estiver estabelecida e uma transação estiver em andamento, executa o commit para salvar as alterações feitas.
     * Se não houver uma transação ativa ou não houver conexão com o banco de dados, retorna falso.
     *
     * @return bool Retorna verdadeiro se os dados foram salvos com sucesso ou se a variável de ambiente HTTP_TEST está definida e se uma transação está em andamento; caso contrário, retorna falso.
     */
    public function salvar(): bool
    {
        if (isset($_SERVER["HTTP_TEST"])) {
            // Se a variável de ambiente HTTP_TEST estiver definida, retorna verdadeiro sem salvar no banco de dados.
            return true;
        }

        if ($this->conexao instanceof PDO && $this->conexao->inTransaction()) {
            try {
                // Se houver uma conexão com o banco de dados e uma transação estiver em andamento, executa o commit para salvar as alterações.
                return $this->conexao->commit();
            } catch (PDOException $e) {
                // Em caso de erro ao realizar o commit, trata a exceção e retorna falso.
                return false;
            }
        }

        // Caso contrário, retorna falso.
        return false;
    }

    /**
     * Estabelece uma conexão com o banco de dados.
     *
     * Esta função é responsável por criar uma conexão com o banco de dados, utilizando as configurações fornecidas.
     * Se uma conexão já existe (armazenada na variável $conexao), ela será reutilizada, evitando a criação de uma nova conexão.
     * O tipo de conexão será salvo na variável $tipo para uso futuro.
     *
     * @return bool Retorna verdadeiro se a conexão foi estabelecida com sucesso ou falso se houve algum erro durante a conexão.
     */
    public function conexao(): bool
    {
        $config = new Config();
        $config = $config->getConfigBanco();

        if (!empty($GLOBALS["_BANCO"]["conexao"])) {
            // Se uma conexão já existe, reutiliza essa conexão e seu tipo.
            $this->conexao = $GLOBALS["_BANCO"]["conexao"];
            $this->tipo = $GLOBALS["_BANCO"]["tipo"];
            return true;
        }

        $this->tipo = $config["tipo"];

        try {
            if ($this->tipo === "mysql") {
                // Cria uma nova conexão PDO para MySQL.
                $this->conexao = new PDO(
                    $config["stringConn"],
                    $config["credenciais"]["login"],
                    $config["credenciais"]["senha"]
                );
            } else {
                // Cria uma nova conexão PDO para outro tipo de banco de dados.
                $caminhoArquivo = explode(":", $config["stringConn"])[1];
                new Arquivo(arquivo: $caminhoArquivo, novo: true);
                $this->conexao = new PDO($config["stringConn"]);
            }

            // Armazena a conexão e seu tipo para uso posterior.
            $GLOBALS["_BANCO"]["conexao"] = $this->conexao;
            $GLOBALS["_BANCO"]["tipo"] = $this->tipo;

            return true;
        } catch (Exception $e) {
            // Em caso de erro, registra o erro no log e retorna falso.
            new Log("Erro ao se conectar à base de dados " . $e, $this->nomeArquivo, "conexao");
            return false;
        }
    }

    /**
     * Insere dados na tabela do banco de dados.
     *
     * Esta função é responsável por inserir dados em uma tabela específica do banco de dados, com base nos dados fornecidos.
     * Antes de executar a inserção, verifica se existem dados válidos para inserir, se a tabela está definida e se ela existe no banco de dados.
     * Se algum dos critérios de validação falhar, registra os erros no log e retorna falso.
     * Caso contrário, adiciona automaticamente campos de criação e modificação de acordo com a estrutura da tabela, se aplicável.
     * Gera o script SQL de inserção e executa a consulta para inserir os dados.
     * Registra a ação de inserção na tabela de log para fins de registro.
     *
     * @param array $dados Um array associativo contendo os dados a serem inseridos ['campo' => 'valor', 'campo2' => 'valor2', ...].
     * @param string $tabela O nome da tabela onde os dados serão inseridos.
     * @return bool|int Retorna false se houver falha na inserção ou o ID do registro inserido (caso bem-sucedido).
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
        if (!$this->existeTabela(tabela: $tabela)) {
            $erros[] = "Tabela {$tabela} inexistente";
        }
        if (!empty($erros)) {
            // Registra os erros no log e retorna falso.
            new Log(implode(", ", $erros), $this->nomeArquivo, "insert");
            return false;
        }

        // Adiciona campos criados e modificados automaticamente, se existirem na tabela e não foram fornecidos.
        $camposTabela = $this->detTabela(tabela: $tabela);
        foreach ($camposTabela as $campo) {
            if ($campo["nome"] === "criado" || $campo["nome"] === "modificado" && !array_key_exists($campo["nome"], $dados)) {
                $dados[$campo["nome"]] = date("Y-m-d H:i:s");
            }
        }

        // Gera o script SQL para a inserção.
        $campos = implode(", ", array_keys($dados));
        $valores = "'" . implode("', '", $dados) . "'";
        $query = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

        // Executa a consulta para inserir os dados.
        $retorno = $this->query(query: $query);

        // Registra a ação de inserção na tabela de log para fins de registro.
        $this->registrarLog(tabela: $tabela, acao: "INSERT", query: json_encode(["dados" => $dados]));

        if ($retorno === false) {
            // Registra no log em caso de falha na inserção e retorna falso.
            new Log("Não foi possível inserir os dados na base de dados", $this->nomeArquivo, "insert");
            return false;
        }

        // Retorna o ID do registro inserido (se bem-sucedido).
        return $retorno;
    }

    /**
     * Função para realizar uma consulta no banco de dados.
     *
     * @param array $arr Array contendo os dados da consulta:
     *   Índices do array:
     *   - array campos: Campos que serão listados ["campo1", "campo2", "campo3", ...]
     *   - string tabela: Nome da tabela
     *   - string as: Alias da tabela
     *   - array join: Joins da tabela ["join1", "join2", "join3", ...]
     *   - array igual: Campos que serão pesquisados ["campo" => "valor", "campo2" => "valor", ...]
     *   - string where: String que será adicionada após o WHERE
     *   - string order: String que será adicionada após o ORDER BY
     * @return array|false Retorna um array com os resultados da consulta ou false se falhar.
     */
    public function select(array $arr = []): array|false
    {
        if (empty($arr["tabela"])) {
            new Log("Tabela não definida para listar", $this->nomeArquivo, "select");
            return false;
        }

        if (!$this->existeTabela($arr["tabela"])) {
            new Log("Tabela {$arr["tabela"]} não encontrada para listar", $this->nomeArquivo, "select");
            return false;
        }

        // Cria uma instância da classe SelectHelper
        $selectHelper = new SelectHelper();

        // Monta os componentes da consulta usando os métodos da classe SelectHelper
        $campos = $selectHelper->campos($arr);
        $tabela = $selectHelper->tabela($arr);
        $join = $selectHelper->join($arr);
        $where = isset($arr["igual"]) ? $this->where($arr["igual"]) : '';
        $where .= isset($arr["where"]) ? $this->where($arr["where"]) : '';
        $whereClause = empty($where) ? "" : "WHERE " . $where;

        $order = isset($arr["order"]) ? " ORDER BY {$arr["order"]}" : '';

        // Construção da consulta SQL
        $query = "SELECT {$campos} FROM {$tabela} {$join} {$whereClause} {$order}";

        // Executa a query de consulta e retorna o resultado
        return $this->query($query);
    }

    /**
     * Função para atualizar os dados na tabela do banco de dados.
     *
     * @param string $tabela Nome da tabela a ser atualizada.
     * @param array $dados Array associativo contendo os dados a serem atualizados.
     *                     Exemplo: ["campo" => "valor", "campo" => "valor"].
     * @param array|string $where Condições para a atualização dos dados.
     * @return bool Retorna true se a atualização foi bem-sucedida e false caso contrário.
     */
    public function update(string $tabela, array $dados, $where): bool
    {
        // Verifica se há dados a serem atualizados
        if (empty($dados)) {
            new Log("Sem dados para atualizar", $this->nomeArquivo, "update");
            return false;
        }

        // Verifica se foi especificada a tabela
        if (empty($tabela)) {
            new Log("Nenhuma tabela definida para atualizar os dados", $this->nomeArquivo, "update");
            return false;
        }

        // Verifica se a tabela existe
        if (!$this->existeTabela($tabela)) {
            new Log("Tabela {$tabela} inexistente", $this->nomeArquivo, "update");
            return false;
        }

        // Define valores para campos que são preenchidos pelo framework, como "modificado"
        $campos = $this->detTabela($tabela);
        foreach ($campos as $campo) {
            if ($campo["nome"] === "modificado") {
                $dados[$campo["nome"]] = date("Y-m-d H:i:s");
            }
        }

        // Gera a lista de campos e valores a serem atualizados
        $camposValores = [];
        foreach ($dados as $key => $value) {
            $camposValores[] = "`{$key}` = '{$value}'";
        }
        $camposValores = implode(", ", $camposValores);

        // Define as condições WHERE para a atualização
        $dadosWhere = $this->where($where);

        // Monta a query de UPDATE diretamente com o nome da tabela recebida
        $query = "UPDATE {$tabela} SET {$camposValores} WHERE {$dadosWhere}";

        // Registra a atualização na tabela de log
        $this->registrarLog($tabela, "UPDATE", json_encode(["dados" => $dados, "where" => $where]));

        // Executa a query de atualização e retorna o resultado
        return $this->query($query);
    }

    /**
     * Função para deletar registros da tabela do banco de dados.
     *
     * @param string $tabela Nome da tabela onde os registros serão deletados.
     * @param array|string $where Condições para a deleção dos registros.
     * @return bool Retorna true se a deleção foi bem-sucedida e false caso contrário.
     */
    public function delete(string $tabela, $where): bool
    {
        // Verifica se foi especificada a tabela
        if (empty($tabela)) {
            new Log("Nenhuma tabela definida para deletar dados", $this->nomeArquivo, "delete");
            return false;
        }

        // Verifica se a tabela existe
        if (!$this->existeTabela($tabela)) {
            new Log("Tabela {$tabela} inexistente", $this->nomeArquivo, "delete");
            return false;
        }

        // Cria a query de DELETE com as condições WHERE
        $query = "DELETE FROM {$tabela} WHERE {$this->where($where)}";

        // Executa a query de DELETE e verifica se ocorreu algum erro
        if ($this->query($query) === false) {
            new Log("Erro ao executar script de delete", $this->nomeArquivo, "delete");
            return false;
        }

        // Registra a deleção na tabela de log
        $this->registrarLog($tabela, "DELETE", json_encode(["where" => $where]));

        return true;
    }

    /**
     * Executa uma query SQL no banco de dados.
     *
     * @param string $query A consulta SQL a ser executada.
     * @return bool|array|int|PDOStatement Retorna um dos seguintes:
     *    - bool: Sucesso ou falha da execução da query.
     *    - array: Dados resultantes da consulta SELECT.
     *    - int: Último ID inserido em caso de query INSERT.
     *    - PDOStatement: Objeto PDO para consultas especiais (SHOW, DESC, PRAGMA).
     */
    public function query(string $query): bool|array|int|PDOStatement
    {
        // Verifica a conexão com o banco de dados
        if (!$this->conexao()) {
            return false;
        }

        // Converte a query para o formato adequado para SQLite, se aplicável
        if ($this->tipo == "sqlite") {
            $query = $this->sqlite($query);
        }

        try {
            // Prepara e executa a consulta
            $stmt = $this->conexao->prepare($query);

            // Verifica o tipo da consulta para tratamento específico
            $tipoConsulta = strtoupper(explode(" ", $query)[0]);

            switch ($tipoConsulta) {
                case "INSERT":
                    // Executa a query INSERT e retorna o último ID inserido
                    $stmt->execute();
                    return $this->conexao->lastInsertId();
                case "SELECT":
                case "SHOW":
                case "DESC":
                case "PRAGMA":
                    // Executa a query e retorna os resultados como array associativo
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                case "UPDATE":
                case "DELETE":
                    // Executa a query UPDATE ou DELETE e retorna true se for bem-sucedida
                    $stmt->execute();
                    return true;
                default:
                    // Executa a query de outros tipos e retorna o resultado
                    $stmt->execute();
                    return $stmt;
            }
        } catch (PDOException $e) {
            // Registra os erros no log em caso de falha na execução da consulta
            new Log("Erro ao executar a consulta: " . $e->getMessage(), $this->nomeArquivo, "query");
            return false;
        }
    }


    /**
     * Converte uma query MySQL para uma query SQLite.
     *
     * @todo Validar outros tipos de scrips que podem dar erro
     * @param string $query Query MySQL a ser convertida.
     * @return string Query SQLite convertida.
     */
    private function sqlite(string $query): string
    {
        // Substitui AUTO_INCREMENT por AUTOINCREMENT
        $query = str_replace("AUTO_INCREMENT", "AUTOINCREMENT", $query);

        // Substitui INT por INTEGER
        $query = preg_replace("/\bINT\b(\([a-zA-Z0-9]+\))?/", "INTEGER$1", $query);

        // Converte DESC para PRAGMA table_info() no SQLite
        $queryType = strtoupper(explode(" ", $query)[0]);

        if ($queryType === "DESC") {
            $tableName = trim(explode(" ", $query)[1]);
            $query = "PRAGMA table_info({$tableName})";
        } else {
            // Lidar com outros tipos de consulta não tratados
        }

        return $query;
    }

    /**
     * Função para montar a cláusula WHERE da query SQL.
     *
     * @param string|array $where Dados para a cláusula WHERE
     * @return string Query WHERE
     */
    private function where(string|array $where): string
    {
        if (is_array($where)) {
            $dadosWhere = [];

            foreach ($where as $key => $val) {
                // Verifica se o valor é inteiro, booleano ou string
                if (is_int($val) || is_bool($val)) {
                    $dadosWhere[] = "{$key} = {$val}";
                } else {
                    // Escapa e trata adequadamente strings
                    $escapedValue = $this->conexao->quote($val);
                    $dadosWhere[] = "{$key} = {$escapedValue}";
                }
            }

            return implode(" AND ", $dadosWhere);
        } elseif (is_string($where)) {
            // Se já for uma string, retorna diretamente (permitindo condições personalizadas)
            return $where;
        }

        // Retorna vazio caso nenhum critério seja atendido
        return '';
    }

    /**
     * Função para listar os campos de uma tabela.
     *
     * @param string $tabela Nome da tabela.
     * @return array Detalhes dos campos da tabela.
     */
    public function detTabela(string $tabela): array
    {
        // Verifica se a tabela está presente no banco de dados
        if (!in_array($tabela, $this->getTabelas())) {
            return [];
        }

        // Obtém os detalhes dos campos com base no tipo de banco de dados
        if ($this->tipo == "mysql") {
            $campos = [];
            // Obtém detalhes dos campos da tabela no MySQL usando a consulta "DESC tabela"
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
        } elseif ($this->tipo == "sqlite") {
            $campos = [];
            // Obtém detalhes dos campos da tabela no SQLite usando PRAGMA table_info
            foreach ($this->query(query: "PRAGMA table_info ('{$tabela}')") as $campo) {
                if ($campo["type"] == "INTEGER") {
                    $campo["type"] = "int(11)";
                }
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
     * Função para listar as tabelas do banco.
     *
     * @return array Array com o nome das tabelas.
     */
    public function getTabelas(): array
    {
        // Verifica o tipo de banco de dados e obtém as tabelas de acordo com o tipo
        if ($this->tipo == "mysql") {
            $retorno = [];
            // Obtém o nome das tabelas no MySQL usando a consulta "SHOW TABLES"
            foreach ($this->query(query: "SHOW TABLES") as $tabela) {
                foreach ($tabela as $nome) {
                    $retorno[] = $nome;
                }
            }
            return $retorno;
        } elseif ($this->tipo == "sqlite") {
            $retorno = [];
            // Obtém o nome das tabelas no SQLite usando a consulta "SELECT * FROM sqlite_master WHERE type='table'"
            foreach ($this->query(query: "SELECT * FROM sqlite_master WHERE type='table'") as $tabela) {
                if ($tabela["name"] != "sqlite_sequence") {
                    $retorno[] = $tabela["name"];
                }
            }
            return $retorno;
        } else {
            return [];
        }
    }

    /**
     * Valida se uma tabela existe no banco de dados.
     *
     * @param string $tabela Nome da tabela a ser verificada.
     * @return bool Retorna true se a tabela existe e false caso contrário.
     */
    public function existeTabela(string $tabela): bool
    {
        return in_array($tabela, $this->getTabelas());
    }

    /**
     * Registra uma ação na tabela de log.
     *
     * @param string $tabela Tabela na qual a ação ocorreu.
     * @param string $acao Ação realizada.
     * @param string $query Consulta realizada.
     * @return bool Retorna true se o registro do log foi inserido com sucesso e false caso contrário.
     */
    private function registrarLog(string $tabela, string $acao, string $query): bool
    {
        // Verifica se não é a tabela de log
        if (substr($tabela, -4) == "_log") {
            return true;
        }

        // Verifica e cria a tabela de log, se ainda não existir
        if (!$this->existeTabela($tabela . "_log")) {
            $queryTabelaLog = "CREATE TABLE IF NOT EXISTS {$tabela}_log (
            `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `acao` varchar(255) NOT NULL,
            `query` LONGTEXT,
            `ip` varchar(255),
            `criado` datetime
        );";

            $this->query($queryTabelaLog);
        }

        // Valida e define o endereço IP do usuário
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        // Insere o registro de log na tabela correspondente
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
     * Retorna os campos da query.
     *
     * @param array $arr Array do select.
     * @return string Alias utilizado em fields.
     */
    public function campos(array $arr): string
    {
        $as = $this->alias($arr);

        if (isset($arr["join"])) {
            $as = null;
        }

        if (!empty($as)) {
            $as = "$as.";
        }

        // Define os campos que serão listados
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
     * Obtém o nome da tabela no select.
     *
     * @param array $arr Array do select.
     * @return string Nome da tabela.
     */
    public function tabela(array $arr): string
    {
        $as = $this->alias($arr);

        return "{$arr["tabela"]} {$as}";
    }

    /**
     * Obtém o alias da query.
     *
     * @param array $arr Array do select.
     * @return string Alias da query.
     */
    public function alias(array $arr): string
    {
        // Verifica o alias
        if (isset($arr["as"])) {
            return $arr["as"];
        }

        return "";
    }

    /**
     * Monta as strings de join.
     *
     * @param array $arr Array do select.
     * @return string String com os joins.
     */
    public function join(array $arr): string
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
