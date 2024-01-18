<?php

class Pasta
{
    // Props
    private string $caminho;
    private bool $temporaria = false;

    // Metodos magicos

    public function __construct(string $caminho, bool $criar = false, bool $temp = false)
    {
        $this->caminho = $caminho;

        $this->setCaminho($caminho);
        $this->setTemporaria($temp);

        if ($criar) {
            $this->criar();
        }
    }

    public function __destruct()
    {
        if ($this->temporaria) {
            $this->excluir();
        }
    }

    public function __get(string $prop): mixed
    {
        switch ($prop) {
            case "caminho":
                return $this->getCaminho();
            case "temporaria":
                return $this->getTemporaria();
            case "nome":
                return $this->getNome();
            default:
                throw new Error("Predocs Erro - Propriedade {$prop} não existe.");
        }
    }

    public function __set(string $prop, mixed $valor): void
    {
        switch ($prop) {
            case "caminho":
                $this->setCaminho($valor);
                break;
            case "temporaria":
                $this->setTemporaria($valor);
                break;
            default:
                throw new Error("PredocsErro - Propriedade {$prop} não pode ser definida.");
        }
    }

    // Gets

    protected function getCaminho(): string
    {
        return $this->caminho;
    }

    protected function getTemporaria(): bool
    {
        return $this->temporaria;
    }

    protected function getNome(): string
    {
        return basename($this->caminho);
    }

    // Sets

    protected function setCaminho(string $caminho): bool
    {
        if (empty($caminho)) {
            throw new InvalidArgumentException("PredocsErro - O caminho da pasta não pode ser vazio.");
        }

        $this->caminho = $caminho;
        return true;
    }

    protected function setTemporaria(bool $temp): bool
    {
        $this->temporaria = $temp;
        return true;
    }

    // Internas

    private function excluirPasta(string $caminho): bool
    {
        if (!is_dir($caminho)) {
            throw new InvalidArgumentException("PredocsErro - O caminho não é uma pasta válida: {$caminho}");
        }

        $arquivos = glob($caminho . '/*');
        foreach ($arquivos as $arquivo) {
            if (is_dir($arquivo)) {
                $this->excluirPasta($arquivo);
            } else {
                if (!unlink($arquivo)) {
                    throw new RuntimeException("PredocsErro - Não foi possível excluir o arquivo: {$arquivo}");
                }
            }
        }

        if (rmdir($caminho)) {
            return true;
        } else {
            throw new RuntimeException("PredocsErro - Não foi possível excluir a pasta: {$caminho}");
        }
    }

    // Externas

    public function criar(int $permissao = 0777): bool
    {
        if ($this->existe()) {
            return true;
        }

        $criacao = mkdir($this->caminho, $permissao, true);
        if ($criacao) {
            return true;
        } else {
            throw new RuntimeException("PredocsErro - Não foi possível criar a pasta {$this->caminho}");
        }
    }

    public function existe(): bool
    {
        return is_dir($this->caminho);
    }

    public function excluir(): bool
    {
        if (!$this->existe()) {
            return true;
        }

        return $this->excluirPasta($this->caminho);
    }

    public static function listarArquivos(string $pasta = null): array
    {
        $pasta = new Pasta($pasta, false, false);

        if (!$pasta->existe()) {
            return [];
        }

        $arquivos = scandir($pasta->caminho);
        $arquivos = array_diff($arquivos, ['.', '..']);

        return $arquivos;
    }

    public static function listarArquivosRecursivo(string $pasta = null): array
    {
        $pasta = new Pasta($pasta);

        if (!$pasta->existe()) {
            return [];
        }

        $arquivos = glob($pasta->caminho . '/**/*', GLOB_BRACE);
        $arquivos = array_filter($arquivos, 'is_file');
        shuffle($arquivos);

        return array_values($arquivos);
    }
}
