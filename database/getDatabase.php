<?php

namespace DatabasePredocs;

// Inclui o arquivo de configuração e inicializa a conexão com o banco de dados
include_once __DIR__ . "/../api/Core/Database.php";
include_once __DIR__ . "/../api/Core/Settings.php";

use Predocs\Core\Database;

class Migration
{
    private string $path;
    private Database $database;
    private string $migrationTable = "migration";

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->database = new Database();

        $this->database->inicializeTransaction();
        $this->run();
        $this->database->save();
    }

    public function run()
    {
        try {
            $files = $this->getFiles();
            foreach ($files as $file) {
                if ($this->migrationExists($file)) {
                    echo "Migration $file already exists" . PHP_EOL;
                    continue;
                }
                echo "Running migration $file" . PHP_EOL;
                $this->runSql($this->getQuery($file));
                $this->insertMigration($file);
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
            $this->database->rollback();
        }
    }

    private function runSql(string $query)
    {
        $this->database->run($query);
    }

    private function getFiles(): array
    {
        return array_diff(scandir($this->path), ['.', '..']);
    }

    private function getQuery(string $file): string
    {
        return file_get_contents($this->path . '/' . $file);
    }

    private function insertMigration(string $name): void
    {
        if ($this->database->existTable($this->migrationTable)) {
            $this->database->insert($this->migrationTable, ["name" => $name]);
        }
    }

    public function migrationExists(string $name): bool
    {
        if ($this->database->existTable($this->migrationTable)) {
            $query = "SELECT * FROM migration WHERE name = :name";
            $result = $this->database->list($query, [":name" => $name]);
            return !empty($result);
        }
        return false;
    }
}
