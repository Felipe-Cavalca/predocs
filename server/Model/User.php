<?php

//! MODELO DE EXEMPLO

namespace Predocs\Model;

use Predocs\Core\Database;

/**
 * Classe de representação do usuário
 *
 * Classe responsável por realizar a comunicação com o banco de dados
 *
 * @package Predocs\Model
 * @version 1.0.0
 * @since 1.0.0
 */
class User
{
    private string $table = "user";
    private Database $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function getById(int $id)
    {
        return $this->serach(["id" => $id])[0] ?? [];
    }

    public function getByEmail(string $email)
    {
        return $this->serach(["email" => $email])[0] ?? [];
    }

    public function getAll()
    {
        return $this->database->list("SELECT * FROM $this->table");
    }

    public function serach(array $conditions)
    {
        $sql = "SELECT * FROM $this->table WHERE " . $this->database->where($conditions);
        return $this->database->list($sql, $conditions);
    }

    public function insert(array $data)
    {
        return $this->database->insert($this->table, $data);
    }
}
