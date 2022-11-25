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
     * @version 1
     * @access public
     * @return bool
     */
    public function installBanco(): bool
    {
        $configBanco = $this->config->getConfigBanco();

        if (!$configBanco["instalado"]) {
            if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/database/")) {
                new Log("Erro ao criar tabelas");
                return false;
            }

            if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/data/")) {
                new Log("Erro ao inserir dados na base de dados");
                return false;
            }

            if ($configBanco["tipo"] == "mysql") {
                if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/triggers/")) {
                    new Log("Erro ao adicionar triggers na base de dados");
                    return false;
                }
            }

            $this->config->setConfigBanco(["instalado" => true]);
        }

        new Log("Banco instalado com sucesso");
        return true;
    }

    /**
     * Roda scripts para realizar a atulização do banco
     * @version 1
     * @access public
     * @return bool
     */
    function updateBanco(): bool
    {
        if (!$this->executaSqlPasta("{$this->config->getCaminho("sql")}/update/", true)) {
            new Log("Erro ao atualizar banco de dados");
            return false;
        }

        new Log("Base de dados atualizada com sucesso");
        return true;
    }

    /**
     * Função para apagar todas as tabelas da base de dados APAGAR EM PRODUÇÂO - APENAS PARA DEV
     * @version 1
     * @access public
     * @return bool
     */
    public function deleteBanco(): bool
    {
        if ($this->banco->tipo == "mysql") {
            foreach ($this->banco->query("show tables") as $tabela) {
                if (!$this->banco->query("DROP TABLE {$tabela};")) {
                    new Log("Erro ao excluir a tabela {$tabela} da base de dados");
                    return false;
                }
            }
        } else {
            $arquivo = new Arquivo(explode(":", $this->config->getConfigBanco()["stringConn"])[1]);
            if (!$arquivo->apagar()) {
                new Log("Erro ao excluir arquivo da base de dados");
                return false;
            }
        }

        $this->config->setConfigBanco(["instalado" => false]);
        new Log("Base de dados apagada");
        return true;
    }

    /**
     * Executa arquivos sql de uma pasta
     * @version 1
     * @access public
     * @param string $pasta caminho para a pasta dos arquivos a serem executados
     * @return bool
     */
    function executaSqlPasta($pasta): bool
    {
        $funcoes = new funcoes();

        foreach ($funcoes->listarArquivos($pasta) as $sql) {

            $script = new Arquivo($pasta . $sql);

            if ($script->getExt() != "sql") continue;

            foreach (explode(";", $script->ler()) as $query) {
                if (empty($this->removeCaracteres($query))) continue;

                $retorno = $this->banco->query("{$query};");

                if (!$retorno) {
                    new Log("Erro ao executar o script {$pasta}{$sql}");
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove caracteres de uma string
     * @param string $str string com dados a serem removidos
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
