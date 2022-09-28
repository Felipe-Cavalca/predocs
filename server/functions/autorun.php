<?php

/**
 * ATENÇÃO !!!! - PODE EDITAR MAS NÃO PODE APAGAR
 */

/**
 * Classe para a execução do auto run
 */
class autorun
{
    public $banco;
    public $config;

    /**
     * Classe para inicializar as variaveis sempre que o objeto é criado
     */
    public function __construct()
    {
        $this->banco = new Banco();
        $this->config = new Config();
    }

    /**
     * Função responsavel por instalar a base de dados
     *
     * @return array ["status" => boolean, "msg" => string]
     */
    function installBanco()
    {
        $configBanco = $this->config->getConfigBanco();

        if (!$configBanco["instalado"]) {
            if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/database/")) {
                new Log("erro na instalação do banco de dados");
                return [
                    "status" => false,
                    "msg" => "erro na instalação do banco de dados"
                ];
            }

            if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/data/")) {
                new Log("erro ao inserir dados na base de dados");
                return [
                    "status" => false,
                    "msg" => "erro ao inserir dados na base de dados"
                ];
            }

            if ($configBanco["tipo"] == "mysql") {
                if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/triggers/")) {
                    new Log("erro ao adicionar triggers na base de dados");
                    return [
                        "status" => false,
                        "msg" => "erro ao adicionar triggers na base de dados"
                    ];
                }
            }

            $this->config->setConfigBanco(["instalado" => true]);
        }

        return [
            "status" => true,
            "msg" => "Banco instalado com sucesso"
        ];
    }

    /**
     * Roda scripts para realizar a atulização do banco
     * @return array ["status" => boolean, "msg" => string]
     */
    function updateBanco()
    {
        if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/update/", true)) {
            new Log("Erro ao atualizar banco de dados");
            return [
                "status" => false,
                "msg" => "Erro ao atualizar banco de dados"
            ];
        }

        return [
            "status" => true,
            "msg" => "Banco atualizado com sucesso"
        ];
    }

    /**
     * Função para apagar todas as tabelas da base de dados APAGAR EM PRODUÇÂO - APENAS PARA DEV
     *
     * @return array ["status" => boolean, "msg" => string]
     */
    function deleteBanco()
    {
        if ($this->banco->tipo == "mysql") {
            foreach ($this->banco->query("show tables")["retorno"] as $tabela) {
                if (!$this->banco->query("DROP TABLE {$tabela};")["retorno"]) {
                    new Log("Erro ao excluir a base de dados");
                    return ["status" => false, "msg" => "Erro ao excluir a base de dados"];
                }
            }
        } else {
            $arquivo = new Arquivo(explode(":", $this->config->getConfigBanco()["stringConn"])[1]);
            if (!$arquivo->apagar()) {
                new Log("Erro ao excluir a base de dados");
                return ["status" => false, "msg" => "Erro ao excluir a base de dados"];
            }
        }

        $this->config->setConfigBanco(["instalado" => false]);
        return ["status" => true, "msg" => "base de dados excluida com sucesso"];
    }

    /**
     * Executa arquivos sql de uma pasta
     * @param string - caminho para a pasta dos arquivos a serem executados
     * @return boolean - caso todos os arquivos foram executados
     */
    function executaSqlPasta($pasta)
    {
        $funcoes = new funcoes();

        foreach ($funcoes->listarArquivos($pasta) as $sql) {

            $script = new Arquivo($pasta . $sql);

            if ($script->getExt() != "sql") continue;

            foreach (explode(";", $script->ler()) as $query) {
                if (empty($this->removeCaracteres($query))) continue;
                if (!$this->banco->query("{$query};")['status']) {
                    new Log("erro ao executar o script {$pasta}{$sql}");
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove caracteres de uma string
     * @param string - string com dados a serem removidos
     * @return string
     */
    function removeCaracteres($str)
    {
        //variavel precisa estar assim pois é para pegar o quebra linha do arquivo .sql (não achei outra forma)
        $enter = "
";
        $tab = "	";
        return str_replace($tab, "", str_replace($enter, "", str_replace(" ", "", $str)));
    }
}
