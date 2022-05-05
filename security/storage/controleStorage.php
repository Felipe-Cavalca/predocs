<?php


$url = explode("/", $_GET["_Pagina"]);
unset($url[0]);
$caminho = implode("/", $url);

if(file_exists("security/storage/files/".$caminho)){
    $arquivo = new Arquivo("security/storage/files/".$caminho);
    $arquivo->renderiza();
}else{
    echo "Não foi possivel recuperar o arquivo";
}
