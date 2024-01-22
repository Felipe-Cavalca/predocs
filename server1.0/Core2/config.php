<?php

class Config
{
    // Props
    private static $initialized = false;
    private static $displayErrors = 1;
    private static $displayStartupErrors = 1;
    private static $errorReporting = E_ALL;

    // Metodos magicos

    public function __construct()
    {
        $this->init();
    }

    public function __get($nome)
    {
        switch ($nome) {
            case 'displayErrors':
                return static::$displayErrors;
            case 'displayStartupErrors':
                return static::$displayStartupErrors;
            case 'errorReporting':
                return static::$errorReporting;
            default:
                return null;
        }
    }

    // Gets

    // Sets

    // Internas

    private static function setHeaders(): void
    {
        header('X-Powered-By: PHP/' . phpversion());
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Expose-Headers: Authorization");
    }

    private static function setIni(): void
    {
        ini_set('display_errors', static::$displayErrors);
        ini_set('display_startup_errors', static::$displayStartupErrors);
        error_reporting(static::$errorReporting);
    }

    private static function criarPastas(): void
    {
        // static::criarPasta(Config::getCaminho("storage"));
        // static::criarPasta(Config::getCaminho("session"));

        // session_save_path(Config::getCaminho("session"));
    }

    // Externas

    final public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        static::setIni();
        static::setHeaders();
        static::criarPastas();

        self::$initialized = true;
    }
}
