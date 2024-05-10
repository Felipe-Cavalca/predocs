<?php

namespace Predocs\Core;

use Exception;

/**
 * Classe de configuração do sistema
 *
 * Esta classe é responsável por gerenciar as configurações do sistema.
 * Ela fornece métodos para obter e definir configurações.
 *
 * @package Predocs\Core
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 *
 */
final class Settings
{
    private static bool $initialized = false;

    public function __construct()
    {
        $this->init();
    }

    public function __get($name)
    {
        switch ($name) {
            case "database":
                return $this->getSettingsDatabase();
            case "app":
                return $this->getSettingsApp();
            default:
                return $this->getEnv($name);
        }
    }

    protected static function getEnv(string $param): mixed
    {
        return getenv($param) ?: null;
    }

    private static function setHeaders(): void
    {
        header("X-Powered-By: PHP/" . phpversion());
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Expose-Headers: Authorization");
    }

    private static function iniSet(): void
    {
        ini_set("display_errors", static::getEnv("PHP_DISPLAY_ERRORS"));
        ini_set("display_startup_errors", static::getEnv("PHP_DISPLAY_STARTUP_ERRORS"));
    }

    public static function init(): void
    {
        // Valida se já foi inicializado
        if (self::$initialized) {
            return;
        }

        static::iniSet();
        static::setHeaders();

        self::$initialized = true;
    }

    private function getSettingsDatabase()
    {
        $driver = $this->getEnv("MYSQL_DRIVER");

        if ($driver == "MySQL") {
            return [
                "driver" => "mysql",
                "host" => static::getEnv("MYSQL_HOST"),
                "port" => static::getEnv("MYSQL_PORT"),
                "database" => static::getEnv("MYSQL_DATABASE"),
                "username" => static::getEnv("MYSQL_USER"),
                "password" => static::getEnv("MYSQL_PASSWORD"),
            ];
        } else if ($driver == "sqlite") {
            return [
                "driver" => "sqlite",
                "database" => static::getEnv("DB_DATABASE"),
            ];
        } else {
            throw new Exception("Variável de ambiente MYSQL_HOST não encontrada");
        }
    }

    private function getSettingsApp(): array
    {
        // Faz um curl para pegar as variaveis do APP
        $url = "https://app/config/app.json"; // url para as variaveis do APP
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
