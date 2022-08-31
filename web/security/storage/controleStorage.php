<?php


$url = explode("/", $_GET["_Pagina"]);
unset($url[0]);
$caminho = implode("/", $url);

if(file_exists("security/storage/files/".$caminho)){
    $arquivo = new Arquivo($caminho, false);
    $arquivo->renderiza();
}else{
    echo "Não foi possivel recuperar o arquivo";
}
