<?php

class Config
{
    /**
     * Retorna um objeto Arquivo com base no nome do arquivo passado.
     *
     * @param string $arquivo Nome do arquivo de configuração.
     * @return Arquivo Objeto Arquivo.
     */
    public static function fileConfig(string $arquivo = ""): Arquivo
    {
        $arquivoConfig = new Arquivo(static::getCaminho("config") . "/{$arquivo}.json");

        if ($arquivoConfig->existe() !== false) {
            return $arquivoConfig;
        }

        $modelo = new Arquivo(static::getCaminho("model/config") . "/{$arquivo}.json");
        $arquivoConfig->criar();
        $arquivoConfig->escrever($modelo->ler());

        return $arquivoConfig;
    }

    /**
     * Retorna um array com as configurações do banco de dados.
     *
     * @return array Array com as configurações do banco.
     */
    public static function getConfigBanco(): array
    {
        $config = static::fileConfig("banco")->ler();

        $retorno["tipo"] = $config["tipo"];
        $retorno["nome"] = $config["nome"];
        $retorno["instalado"] = $config["instalado"];

        switch ($config["tipo"]) {
            case "mysql":
                $retorno["credenciais"] = $config["mysql"]["credenciais"];
                $retorno["stringConn"] = "mysql:host={$config["mysql"]["host"]}:{$config["mysql"]["porta"]};dbname={$config["nome"]}";
                break;
            case "sqlite":
            default:
                $retorno["stringConn"] = "sqlite:" . static::getCaminho("sqlite") . "/{$config["nome"]}.db";
                break;
        }

        return $retorno;
    }

    /**
     * Salvar as alterações feitas nas configurações do banco
     *
     * @param array $configs Array de configurações com os dados de configuração do banco
     * @return bool
     */
    public static function setConfigBanco(array $configs): bool
    {
        return static::fileConfig("banco")->adicionar($configs);
    }

    /**
     * Retorna as configurações de cache.
     *
     * @return array|string Array com os dados de cache.
     */
    public static function getConfigCache(): array|string
    {
        return static::fileConfig("cache")->ler();
    }

    /**
     * Retorna as configurações gerais.
     *
     * @return array Array com os dados de configuração.
     */
    public static function getConfig(): array
    {
        return static::fileConfig("config")->ler();
    }

    /**
     * Retorna o nome do ambiente.
     *
     * @return string Nome do ambiente.
     */
    public static function ambiente(): string
    {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * Retorna o caminho para o diretório especificado.
     *
     * @param string $tipo Nome do diretório desejado.
     * @return string Caminho até o diretório especificado.
     */
    public static function getCaminho(string $tipo = ""): string
    {
        switch ($tipo) {
            case "controller":
                return "./controllers";
            case "install/config":
                return "./install/config";
            case "log":
            case "config":
            case "storage":
            case "session":
                return "./data/" . static::ambiente() . "/{$tipo}";
            case "sqlite":
                return "./data/" . static::ambiente() . "/database";
            case "app":
                return "./../app";
            case "sql":
            case "functions":
            case "includes":
            default:
                return "./{$tipo}";
        }
    }
}
