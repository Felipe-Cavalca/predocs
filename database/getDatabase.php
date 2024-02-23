<?php

namespace DatabasePredocs;

// Inclui o arquivo de configuração e inicializa a conexão com o banco de dados
include_once __DIR__ . "/../server/Core/Database.php";
include_once __DIR__ . "/../server/Core/Settings.php";

// Importa as classes necessárias
use Predocs\Core\Database;

// Inicializa a conexão com o banco de dados
$database = new Database();
