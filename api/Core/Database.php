<?php

namespace Predocs\Core;

use PDO;
use Predocs\Core\Settings;

/**
 * Classe Banco
 *
 * Esta classe é responsável por gerenciar a conexão com o banco de dados.
 *
 * @package Predocs\Core
 * @version 1.0.0
 * @since 1.0.0
 */
class Database
{
    private static PDO $conn;
    private static Settings $settings;
    private static string $driver;

    public function __construct()
    {
        if (empty(self::$settings)) {
            self::$settings = new Settings();
        }

        if (empty(self::$conn)) {
            self::$conn = $this->conn();
        }

        if (empty(self::$driver)) {
            self::$driver = self::$settings->database["driver"];
        }
    }

    private static function conn(): PDO
    {
        $dataConn = self::$settings->database;

        switch ($dataConn["driver"]) {
            case "sqlite":
                return new PDO("sqlite:" . $dataConn["database"]);
            case "mysql":
            default:
                return new PDO(
                    "mysql:host={$dataConn["host"]}:{$dataConn["port"]};dbname={$dataConn["database"]};charset=utf8",
                    $dataConn["username"],
                    $dataConn["password"]
                );
        }
    }

    public function where(array $conditions): string
    {
        $where = [];
        foreach (array_keys($conditions) as $field) {
            $where[] = "{$field} = :{$field}";
        }
        return implode(" AND ", $where);
    }

    public function inicializeTransaction(): bool
    {
        if (
            self::$conn instanceof PDO &&
            !self::$conn->inTransaction()
        ) {
            return self::$conn->beginTransaction();
        }
        return false;
    }

    public function rollback(): bool
    {
        if (
            self::$conn instanceof PDO &&
            self::$conn->inTransaction()
        ) {
            return self::$conn->rollBack();
        }
        return false;
    }

    public function save(): bool
    {
        if (
            self::$conn instanceof PDO &&
            self::$conn->inTransaction()
        ) {
            return self::$conn->commit();
        }
        return false;
    }

    public function run(string $sql, array $params = []): bool
    {
        $stmt = self::$conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function list(string $sql, array $params = []): array
    {
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listOne(string $sql, array $params = []): array
    {
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $data): bool
    {
        if ($this->existField($table, "created")) {
            $data["created"] = date("Y-m-d H:i:s");
        }
        if ($this->existField($table, "modified")) {
            $data["modified"] = date("Y-m-d H:i:s");
        }
        $fields = array_keys($data);
        $sql = "INSERT INTO {$table} (" . implode(", ", $fields) . ") VALUES (:" . implode(", :", $fields) . ")";
        return $this->run($sql, $data);
    }

    public function update(string $table, array $data, array $where): bool
    {
        if ($this->existField($table, "modified")) {
            $data["modified"] = date("Y-m-d H:i:s");
        }

        $sql = "UPDATE {$table} SET ";

        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }

        $sql .= implode(", ", $fields);
        $where = $this->where($where);
        $sql .= " WHERE {$where}";

        $params = array_merge($data, $where);
        return $this->run($sql, $params);
    }

    public function delete(string $table, array $where): bool
    {
        $whereStr = $this->where($where);
        $sql = "DELETE FROM {$table} WHERE {$whereStr}";
        return $this->run($sql, $where);
    }

    public function getDetTable(string $table): array
    {
        if (!in_array($table, $this->getTables())) {
            return [];
        }

        $fields = [];

        switch (static::$driver) {
            case "sqlite":
                $query = $this->list("PRAGMA table_info('{$table}')");
                foreach ($query as $field) {
                    if ($field["type"] == "INTEGER") {
                        $field["type"] = "int(11)";
                    }
                    $fields[] = [
                        "name" => $field["name"],
                        "type" => $field["type"],
                        "null" => !$field["notnull"],
                        "default" => $field["dflt_value"],
                        "pk" => $field["pk"] == 1
                    ];
                }
            case "mysql":
            default:
                $query = $this->list("DESC {$table}");
                foreach ($query as $field) {
                    $fields[] = [
                        "name" => $field["Field"],
                        "type" => $field["Type"],
                        "null" => $field["Null"] == "YES",
                        "default" => $field["Default"],
                        "pk" => $field["Extra"] == "auto_increment"
                    ];
                }
        }

        return $fields;
    }

    public function getTables(): array
    {
        $tables = [];

        switch (static::$driver) {
            case "sqlite":
                $query = $this->list("SELECT * FROM sqlite_master WHERE type='table'");
                foreach ($query as $table) {
                    if ($table["name"] != "sqlite_sequence") {
                        $tables[] = $table["name"];
                    }
                }
                break;
            case "mysql":
            default:
                $query = $this->list("SHOW TABLES");
                $tables = array_column($query, 'Tables_in_' . self::$settings->database["database"]);
                break;
        }

        return $tables;
    }

    public function existTable(string $table): bool
    {
        return in_array($table, $this->getTables());
    }

    public function existField(string $table, string $field): bool
    {
        $fields = array_column($this->getDetTable($table), "name");
        return in_array($field, $fields);
    }
}
