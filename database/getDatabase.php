<?php

namespace DatabasePredocs;

// Inclui o arquivo de configuração e inicializa a conexão com o banco de dados
include_once __DIR__ . "/../Api/Core/Database.php";
include_once __DIR__ . "/../Api/Core/Settings.php";

// Importa as classes necessárias
use Predocs\Core\Database;

// Função para executar SQLs
function runSql($structurePath) {
    $database = new Database();
    $files = scandir($structurePath);

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $sql = file_get_contents($structurePath . '/' . $file);
            $database->run($sql);
        }
    }
}
