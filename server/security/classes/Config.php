<?php

Class Config {
    //config do app
    public $nomeApp = "lis";
    
    /**
     * Configurar variaveis do banco
     * @return arr - dados do banco
     */
    public function getConfigBanco(){
        global $_ARQUIVOS;
        return $_ARQUIVOS->getJson("../includes/config.json")["banco"];
    }
}