<?php

Class Config {
    //config do app
    public $nomeApp = "lis";
    
    /**
     * Configurar variaveis do banco
     * @return arr - dados do banco
     */
    public function getConfigBanco(){
        //setando variaveis do banco
        $banco = [];
        $banco["host"] = "localhost";
        $banco["porta"] = "3307";
        $banco["nome"] = "lis";
        $banco["credencial"]["nome"] = "root";
        $banco["credencial"]["senha"] = "";
        return $banco;
    }
}