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

    protected static function getEnv(string $param): mixed
    {
        return getenv($param) ?: $_ENV[$param] ?: $_SERVER[$param];
    }

    private static function setEnvironmentVariables(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception("File .env Not Found");
        }

        $lanes = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lanes as $lane) {
            if (strpos(trim($lane), "#") === 0) {
                continue;
            }

            list($name, $value) = explode("=", $lane, 2);
            $name = trim($name);
            $value = trim($value);

            putenv(sprintf("%s=%s", $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    private static function listFilesDotEnv($env = "local"): array
    {
        return glob(__DIR__ . "/../envs/{$env}*.env");
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
        ini_set("display_errors", static::getEnv("PHP.DISPLAY_ERRORS"));
        ini_set("display_startup_errors", static::getEnv("PHP.DISPLAY_STARTUP_ERRORS"));
    }

    public static function init(): void
    {
        // Valida se já foi inicializado
        if (self::$initialized) {
            return;
        }

        static::setEnvironmentVariables(__DIR__ . "/../settings.env");

        $arquivosAmbiente = static::listFilesDotEnv();
        foreach ($arquivosAmbiente as $arquivo) {
            static::setEnvironmentVariables($arquivo);
        }

        static::iniSet();
        static::setHeaders();

        self::$initialized = true;
    }

    public function getSettingsDatabase()
    {
        $driver = $this->getEnv("DB.DRIVER");

        if ($driver == "MySQL") {
            return [
                "driver" => "mysql",
                "host" => static::getEnv("DB.HOST"),
                "port" => static::getEnv("DB.PORT"),
                "database" => static::getEnv("DB.DATABASE"),
                "username" => static::getEnv("DB.USERNAME"),
                "password" => static::getEnv("DB.PASSWORD"),
            ];
        } else if ($driver == "sqlite") {
            return [
                "driver" => "sqlite",
                "database" => static::getEnv("DB.DATABASE"),
            ];
        } else {
            throw new Exception("Variável de ambiente DATABASE.DRIVER não encontrada");
        }
    }
}
