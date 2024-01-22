<?php

/**
 * ATENÇÃO !!!! - PODE EDITAR MAS NÃO PODE APAGAR
 */

/**
 * Classe para a execução do auto run
 */
class Autorun
{

    /**
     * Classe para inicializar as variaveis sempre que o objeto é criado
     */
    public function __construct()
    {
    }

    /**
     * Executa scripts sql para criação do banco
     *
     * @return bool
     */
    public function installBanco(): bool
    {
        $configBanco = Config::getConfigBanco();

        if (!$configBanco["instalado"]) {
            if (!$this->executaSqlPasta(Config::getCaminho("sql") . "/database/")) {
                Log::registrar("Erro ao criar tabelas");
                return false;
            }

            if (!$this->executaSqlPasta(Config::getCaminho("sql") . "/data/")) {
                Log::registrar("Erro ao inserir dados na base de dados");
                return false;
            }

            if ($configBanco["tipo"] == "mysql" && !$this->executaSqlPasta(Config::getCaminho("sql") . "/triggers/")) {
                Log::registrar("Erro ao adicionar triggers na base de dados");
                return false;
            }

            Config::setConfigBanco(["instalado" => true]);
        }

        Log::registrar("Banco instalado com sucesso");
        return true;
    }

    /**
     * Roda scripts para realizar a atulização do banco
     *
     * @return bool
     */
    public function updateBanco(): bool
    {
        if (!$this->executaSqlPasta(Config::getCaminho("sql") . "/update/", true)) {
            Log::registrar("Erro ao atualizar banco de dados");
            return false;
        }

        Log::registrar("Base de dados atualizada com sucesso");
        return true;
    }

    /**
     * Função para apagar todas as tabelas da base de dados APAGAR EM PRODUÇÂO - APENAS PARA DEV
     *
     * @return bool
     */
    public function deleteBanco(): bool
    {
        $banco = new Banco();

        if ($banco->tipo == "mysql") {
            foreach ($banco->query("show tables") as $tabela) {
                if (!$banco->query("DROP TABLE {$tabela};")) {
                    Log::registrar("Erro ao excluir a tabela {$tabela} da base de dados");
                    return false;
                }
            }
        } else {
            $arquivo = new Arquivo(explode(":", Config::getConfigBanco()["stringConn"])[1]);
            if (!$arquivo->apagar()) {
                Log::registrar("Erro ao excluir arquivo da base de dados");
                return false;
            }
        }

        Config::setConfigBanco(["instalado" => false]);
        Log::registrar("Base de dados apagada");
        return true;
    }

    /**
     * Executa arquivos sql de uma pasta
     *
     * @param string $pasta caminho para a pasta dos arquivos a serem executados
     * @return bool
     */
    public function executaSqlPasta($pasta): bool
    {
        $banco = new Banco();
        foreach (Funcoes::listarArquivos($pasta) as $sql) {

            $script = new Arquivo($pasta . $sql);

            if ($script->getExt() != "sql") {
                continue;
            }

            foreach (explode(";", $script->ler()) as $query) {
                if (empty($this->removeCaracteres($query))) {
                    continue;
                }

                $retorno = $banco->query("{$query};");

                if (!$retorno) {
                    Log::registrar("Erro ao executar o script {$pasta}{$sql}");
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove caracteres de uma string
     *
     * @param string $str string com dados a serem removidos
     * @return string
     */
    public function removeCaracteres($str)
    {
        //variavel precisa estar assim pois é para pegar o quebra linha do arquivo .sql (não achei outra forma)
        $enter = "
";
        $tab = "	";
        return str_replace($tab, "", str_replace($enter, "", str_replace(" ", "", $str)));
    }
}
