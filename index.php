<?php

//verifica qual das telas serão exibidas ao usuario
if(getInstalou()){
    include ("app/index.html");
}else{
    include("install/index.php");
}

/**
 * Função para pegar se o sistema foi instalado ou não
 * 
 * @return boolean - verifica se o sistema já foi instalado ou não
 */
function getInstalou(){

    //pega as classes de arquivos
    require ('server/security/classes/arquivos.php');
    $_ARQUIVOS = new Arquivos;

    return $_ARQUIVOS->getJson("includes/config.json")["instalou"];
}