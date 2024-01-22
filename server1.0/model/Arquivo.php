<?php

/**
 * Classe para manipulação de arquivos.
 *
 * @package server\model
 * @version 1.0.0
 *
 * @property string $caminho Caminho do arquivo.
 * @property bool $temporaria Se o arquivo é temporário.
 * @property-read string $nome Nome do arquivo.
 * @property-read string $extensao Extensão do arquivo.
 * @property-read int $tamanho Tamanho do arquivo.
 * @property-read int $permissao Permissão do arquivo.
 * @property string $conteudo Conteúdo do arquivo.
 * @property-read string $hashMd5 Hash MD5 do arquivo.
 * @property-read int $modificado Data de modificação do arquivo.
 * @property-read int $acessado Data de acesso do arquivo.
 * @property-read int $criado Data de criação do arquivo.
 * @property-read string $mimeType Mime type do arquivo.
 */
class Arquivo
{
    // Props
    private string $caminho = "";
    private bool $temporaria = false;
    private string $erroArquivoNaoExiste = "PredocsErro - O arquivo não existe.";

    // Metodos magicos

    public function __construct(string $caminho, bool $criar = false, bool $temp = false)
    {
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

    public function __toString(): string
    {
        if ($this->existe()) {
            if ($this->extensao == 'php') {
                include_once $this->caminho;
                return "";
            } else {
                return $this->ler();
            }
        } else {
            throw new Error($this->erroArquivoNaoExiste);
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
            case "extensao":
                return $this->getExtensao();
            case "tamanho":
                return $this->getTamanho();
            case "permissao":
                return $this->getPermissao();
            case "conteudo":
                return $this->getConteudo();
            case "hashMd5":
                return $this->getHashMd5();
            case "modificado":
                return $this->getModificado();
            case "acessado":
                return $this->getAcessado();
            case "criado":
                return $this->getCriado();
            case "mimeType":
                return $this->getMimeType();
            default:
                throw new Error("PredocsErro - Propriedade {$prop} não existe.");
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
            case "conteudo":
                $this->setConteudo($valor);
                break;
            default:
                throw new Error("PredocsErro - Propriedade {$prop} não pode ser definida.");
        }
    }

    // Gets

    protected function getCaminho(): string
    {
        $pathinfo = pathinfo($this->caminho);
        return $pathinfo["dirname"] ?? "./";
    }

    protected function getTemporaria(): bool
    {
        return $this->temporaria;
    }

    protected function getNome(): string
    {
        return basename($this->caminho);
    }

    protected function getExtensao(): string
    {
        $pathinfo = pathinfo($this->caminho);
        return $pathinfo["extension"] ?? "";
    }

    protected function getTamanho(): int
    {
        if ($this->existe()) {
            return filesize($this->caminho);
        } else {
            return 0;
        }
    }

    protected function getPermissao(): int
    {
        if ($this->existe()) {
            return fileperms($this->caminho);
        } else {
            return 0;
        }
    }

    protected function getConteudo(): string
    {
        if ($this->existe()) {
            return file_get_contents($this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    protected function getHashMd5(): string
    {
        if ($this->existe()) {
            return hash_file("md5", $this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    protected function getModificado(): int
    {
        if ($this->existe()) {
            return filemtime($this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    protected function getAcessado(): int
    {
        if ($this->existe()) {
            return fileatime($this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    protected function getCriado(): int
    {
        if ($this->existe()) {
            return filectime($this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    protected function getMimeType(): string
    {
        if ($this->existe()) {
            return mime_content_type($this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    // Sets

    protected function setCaminho(string $caminho): bool
    {
        if (is_string($caminho) && !empty($caminho)) {
            $this->caminho = $caminho;
            return true;
        } else {
            throw new Error("PredocsErro - O caminho do arquivo deve ser uma string válida.");
        }
    }

    protected function setTemporaria(bool $temp): bool
    {
        $this->temporaria = $temp;
        return true;
    }

    protected function setConteudo(string $conteudo): bool
    {
        if ($this->existe()) {
            return file_put_contents($this->caminho, $conteudo);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    // Internas

    private function existeArquivo($caminho): bool
    {
        return file_exists($caminho);
    }

    // Externas

    public function criar(): bool
    {
        if ($this->existe()) {
            return true;
        } else {
            if (touch($this->caminho)) {
                return true;
            } else {
                throw new Error("PredocsErro - Não foi possível criar o arquivo {$this->caminho}");
            }
        }
    }

    public function existe(): bool
    {
        return file_exists($this->caminho);
    }

    public function excluir(): bool
    {
        if ($this->existe()) {
            return unlink($this->caminho);
        } else {
            return true;
        }
    }

    public function renomear(string $novoCaminho): bool
    {
        $caminhoAntigo = $this->caminho;
        $this->setCaminho($novoCaminho);

        if ($this->existe()) {
            throw new Error("PredocsErro - O arquivo já existe.");
        }

        if (rename($caminhoAntigo, $this->caminho)) {
            return true;
        } else {
            throw new Error("PredocsErro - Não foi possível renomear o arquivo.");
        }
    }

    public function mover(string $novoCaminho): bool
    {
        return $this->renomear($novoCaminho);
    }

    public function copiar(string $novoCaminho): bool
    {
        if ($this->existe()) {
            if ($this->existeArquivo($novoCaminho)) {
                throw new Error("PredocsErro - O arquivo já existe.");
            }
            if (copy($this->caminho, $novoCaminho)) {
                return true;
            } else {
                throw new Error("PredocsErro - Não foi possível copiar o arquivo.");
            }
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    public function adicionar(string $conteudo): bool
    {
        if ($this->existe()) {
            return file_put_contents($this->caminho, $conteudo, FILE_APPEND);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    public function upload(string $arquivo): bool
    {
        if ($this->existe()) {
            return move_uploaded_file($arquivo, $this->caminho);
        } else {
            throw new Error($this->erroArquivoNaoExiste);
        }
    }

    public static function criarTemporario(string $conteudo = ""): Arquivo
    {
        $arquivo = new Arquivo(tempnam(sys_get_temp_dir(), "predocs"), false, true);
        $arquivo->escrever($conteudo);
        return $arquivo;
    }
}
