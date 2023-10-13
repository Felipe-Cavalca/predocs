<?php

class Arquivo
{
    /**
     * Caminho até o arquivo.
     * @var string
     * @access private
     */
    private $path;

    /**
     * Constroi a classe do arquivo
     * @version 1
     * @access public
     * @param string $arquivo Nome do arquivo
     * @param bool $novo Geração de um novo arquivo
     * @return void
     */
    public function __construct(string $arquivo, bool $novo = false): void
    {
        $this->path = $arquivo;
        if ($novo) {
            $this->criar();
        }
    }

    /**
     * Função para apagar os dados do arquivo após não usar a classe
     */
    public function __destruct(): void
    {
        $this->path = null;
    }

    /**
     * Função para validadar se o arquivo existe
     * @version 1
     * @access public
     * @return bool
     */
    public function existe(): bool
    {
        return file_exists(filename: $this->path);
    }

    /**
     * Criar um arquivo caso não exista
     * @version 1
     * @access public
     * @return int|bool Quantidade de bytes do arquivo ou false para erro
     */
    public function criar(): int|bool
    {
        if ($this->existe()) {
            return $this->tamanho();
        }
        $this->criaPasta();
        return file_put_contents(filename: $this->path, data: "");
    }

    /**
     * Retorna o tamanho do arquivo em bytes
     * @version 1
     * @access public
     * @return int
     */
    public function tamanho(): int
    {
        if ($this->existe()) {
            return filesize(filename: $this->path);
        }
        return 0;
    }

    /**
     * Realiza o upload de um arquivo
     * @version 1
     * @access public
     * @param string $arquivo Arquivo original quer será enviador
     * @return bool
     */
    public function upload(string $arquivo): bool
    {
        $atual = $this->path;
        $this->path = $arquivo;
        $this->criaPasta();
        if (move_uploaded_file(from: $arquivo, to: $atual)) {
            $this->path = $atual;
            return true;
        }
        return false;
    }

    /**
     * Mover arquivo de pasta
     * @version 1
     * @access public
     * @param string $destino Destino do arquivo
     * @return bool
     */
    public function mover(string $destino): bool
    {
        $atual = $this->path;
        $this->path = $destino;
        $this->criaPasta();
        if (rename(from: $atual, to: $destino)) {
            return true;
        } else {
            $this->path = $atual;
        }
        return false;
    }

    /**
     * Copiar arquivo atual para outro diretorio
     * @version 1
     * @access public
     * @param string $destino Destino do arquivo
     * @return bool
     */
    public function copiar(string $destino): bool
    {
        $atual = $this->path;
        $this->path = $destino;
        $this->criaPasta();
        $this->path = $atual;
        return copy(from: $this->path, to: $destino);
    }

    /**
     * Retorna conteudo do arquivo
     * @version 1
     * @access public
     * @return array|string array para jsons ou string para outros conteudos
     */
    public function ler(): array|string
    {
        if ($this->getExt() == "json") {
            return $this->lerJson();
        } else {
            return $this->lerArquivo();
        }
    }

    /**
     * Escrever conteúdo no arquivo
     * @version 1
     * @access public
     * @param string|array $conteudo conteudo a ser inserido
     * @return bool
     */
    public function escrever(string|array $conteudo): bool
    {
        if ($this->getExt() == "json") {
            return $this->escreverJson(arr: $conteudo);
        } else {
            return $this->escreverArquivo(conteudo: $conteudo);
        }
    }

    /**
     * Escrever conteúdo no final do arquivo
     * @version 1
     * @access public
     * @param string|array $conteudo conteudo que será escrito
     * @return bool
     */
    public function adicionar(string|array $conteudo): bool
    {
        if ($this->getExt()) {
            return $this->escrever(conteudo: array_merge($this->ler(), $conteudo));
        } else {
            return $this->escrever(conteudo: $this->ler() . $conteudo);
        }
    }

    /**
     * Função para ler e exibir o conteudo de um arquivo na tela
     * @version 1
     * @access public
     * @return void
     */
    public function renderiza(): void
    {
        if ($this->getExt() == "php") {
            include_once($this->path);
        } else {
            header("Content-Type: " . $this->getMimeType());
            header("Cache-Control: " . $this->tempoCache());
            readfile(filename: $this->path);
        }
    }

    /**
     * Apaga o arquivo
     * @version 1
     * @access public
     * @return bool
     */
    public function apagar(): bool
    {
        return unlink(filename: $this->path);
    }

    /**
     * retorna o tempo de cache do arquivo
     * @version 1
     * @access public
     * @return string cache a ser colocado no arquivo
     */
    public function tempoCache(): string
    {
        $config = new Config;
        $cache = $config->getconfigCache();

        if (!$cache["ativo"] || isset($cache["excluir"][$this->getExt()])) {
            return "no-cache";
        }

        if (isset($cache["incluir"][$this->getExt()])) {
            return "private, max-age=" . $cache['incluir'][$this->getExt()] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
        }

        return "private, max-age=" . $cache['default'] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
    }

    /**
     * Retorna o mimetype do arquivo
     * @version 1
     * @access public
     * @return string
     */
    public function getMimeType(): string
    {
        $arquivo = $this->path;
        if ($this->existe()) {
            switch ($this->getExt()) {
                case "js":
                    return "application/javascript";
                case "css":
                    return "text/css";
                default:
                    return mime_content_type(filename: $arquivo);
            }
        } else {
            return "text/plain";
        }
    }

    /**
     * Retorna a extenção do arquivo
     * @version 1
     * @access public
     * @return string
     */
    public function getExt(): string
    {
        $array = explode(".", $this->path);
        return end($array);
    }

    /**
     * Retorna o nome do arquivo
     * @version 1
     * @access public
     * @return string
     */
    public function getNome(): string
    {
        $array = explode("/", $this->path);
        return end($array);
    }

    /**
     * string com pastas até o arquivo
     * @version 1
     * @access public
     * @param bool $array true para receber o retorno como array
     * @return string|array
     */
    public function getPasta(bool $array = false): string|array
    {
        $retorno = str_replace(search: $this->getNome(), replace: "", subject: $this->path);
        if ($array) {
            $retorno = explode(separator: "/", string: $retorno);
        }
        return $retorno;
    }

    // ===== Funções auxiliares =================================================

    /**
     * Função para ler o conteudo de um arquivo
     * @version 1
     * @access private
     * @return string o conteudo do arquivo
     */
    private function lerArquivo(): string
    {
        return file_get_contents(filename: $this->path);
    }

    /**
     * Função para escrever no arquivo
     * @version 1
     * @access private
     * @param string o conteudo que será escrito
     * @return bool
     */
    private function escreverArquivo(string $conteudo): bool
    {
        return file_put_contents(filename: $this->path, data: $conteudo);
    }

    /**
     * função para retornar os dados de um arquivo .json
     * @version 1
     * @access private
     * @return array json decodificado
     */
    private function lerJson(): array
    {
        return json_decode($this->lerArquivo(), true);
    }

    /**
     * Função para escrever um arr em um arquivo .json
     * @version 1
     * @access private
     * @param array $arr de dados que serão convertidos em json
     * @return bool
     */
    private function escreverJson(array $arr): bool
    {
        return $this->escreverArquivo(conteudo: is_array($arr) ? json_encode($arr) : $arr);
    }

    /**
     * Função para criar as pastas até o arquivo
     * @version 1
     * @access private
     * @return void
     */
    private function criaPasta(): void
    {
        $pastas = $this->getPasta();
        if (!is_dir($pastas)) {
            mkdir($pastas, 0777, true);
        }
    }
}
