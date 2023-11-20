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
     * Construtor da classe Arquivo.
     *
     * @param string $arquivo Nome do arquivo.
     * @param bool $novo Indica se um novo arquivo será criado (opcional, padrão: false).
     * @return void
     */
    public function __construct(string $arquivo, bool $novo = false)
    {
        $this->path = $arquivo;
        if ($novo) {
            $this->criar();
        }
    }

    /**
     * Função para apagar os dados do arquivo após não usar a classe
     */
    public function __destruct()
    {
        $this->path = null;
    }

    /**
     * Verifica se o arquivo existe.
     *
     * @return bool Retorna true se o arquivo existir, false caso contrário.
     */
    public function existe(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Cria um arquivo se ele não existir.
     *
     * @param string $conteudo Conteúdo a ser escrito no arquivo (opcional, padrão: vazio).
     * @return int|bool Quantidade de bytes do arquivo ou false para erro.
     */
    public function criar(string $conteudo = ''): int|bool
    {
        if ($this->existe()) {
            return $this->tamanho();
        }
        $this->criaPasta();
        return file_put_contents($this->path, $conteudo);
    }


    /**
     * Retorna o tamanho do arquivo em bytes, ou zero se o arquivo não existir.
     * @return int
     */
    public function tamanho(): int
    {
        return filesize($this->path);
    }

    /**
     * Realiza o upload do arquivo representado por esta instância de Arquivo.
     *
     * @param string $arquivo Caminho do arquivo original que será enviado.
     * @return bool Retorna true se o upload for bem-sucedido, false caso contrário.
     */
    public function upload(string $arquivo): bool
    {
        $caminhoOriginal = $this->path;
        $this->path = $arquivo;
        $this->criaPasta();

        if (move_uploaded_file($arquivo, $caminhoOriginal)) {
            $this->path = $caminhoOriginal;
            return true;
        }

        return false;
    }

    /**
     * Move o arquivo representado por esta instância de Arquivo para um novo destino.
     *
     * @param string $destino Novo destino para onde o arquivo será movido.
     * @return bool Retorna true se a operação de movimentação for bem-sucedida, false caso contrário.
     */
    public function mover(string $destino): bool
    {
        $caminhoOriginal = $this->path;
        $this->path = $destino;
        $this->criaPasta();

        if (rename($caminhoOriginal, $destino)) {
            return true;
        } else {
            $this->path = $caminhoOriginal;
        }

        return false;
    }

    /**
     * Copia o arquivo representado por esta instância de Arquivo para outro diretório.
     *
     * @param string $destino O caminho de destino para onde o arquivo será copiado.
     * @return bool Retorna true se a operação de cópia for bem-sucedida, false caso contrário.
     */
    public function copiar(string $destino): bool
    {
        $caminhoOriginal = $this->path;
        $this->path = $destino;
        $this->criaPasta();
        $this->path = $caminhoOriginal;

        return copy($caminhoOriginal, $destino);
    }


    /**
     * Retorna o conteúdo do arquivo representado por esta instância de Arquivo.
     *
     * Se o arquivo tiver extensão ".json", decodifica o conteúdo JSON para um array;
     * caso contrário, lê o conteúdo do arquivo como uma string.
     *
     * @return array|string Retorna um array para arquivos JSON ou uma string para outros tipos de conteúdo.
     */
    public function ler(): array|string
    {
        if ($this->getExt() === "json") {
            return $this->lerJson();
        } else {
            return $this->lerArquivo();
        }
    }

    /**
     * Escreve o conteúdo fornecido no arquivo representado por esta instância de Arquivo.
     *
     * Se o arquivo tiver extensão ".json", o conteúdo será codificado como JSON e escrito no arquivo;
     * caso contrário, o conteúdo será escrito diretamente no arquivo.
     *
     * @param string|array $conteudo O conteúdo a ser inserido no arquivo.
     * @return bool Retorna true se a operação de escrita for bem-sucedida, false caso contrário.
     */
    public function escrever(string|array $conteudo): bool
    {
        if ($this->getExt() === "json") {
            return $this->escreverJson(arr: $conteudo);
        } else {
            return $this->escreverArquivo(conteudo: $conteudo);
        }
    }

    /**
     * Adiciona conteúdo ao final do arquivo representado por esta instância de Arquivo.
     *
     * Se o arquivo tiver extensão ".json", o conteúdo fornecido será mesclado com o conteúdo atual do arquivo;
     * caso contrário, o conteúdo será adicionado ao final do arquivo.
     *
     * @param string|array $conteudo O conteúdo a ser adicionado ao arquivo.
     * @return bool Retorna true se a operação de adição for bem-sucedida, false caso contrário.
     */
    public function adicionar(string|array $conteudo): bool
    {
        if ($this->getExt() === "json") {
            return $this->escrever(conteudo: array_merge($this->ler(), $conteudo));
        } else {
            return $this->escrever(conteudo: $this->ler() . $conteudo);
        }
    }

    /**
     * Exibe o conteúdo do arquivo na tela ou inclui um arquivo PHP, dependendo da extensão.
     *
     * Se a extensão do arquivo for ".php", ele será incluído na página atual;
     * caso contrário, o conteúdo do arquivo será exibido diretamente na tela.
     *
     * @return void
     */
    public function renderiza(): void
    {
        if ($this->getExt() === "php") {
            include_once($this->path);
        } else {
            header("Content-Type: " . $this->getMimeType());
            header("Cache-Control: " . $this->tempoCache());
            readfile($this->path);
        }
    }

    /**
     * Apaga o arquivo representado por esta instância de Arquivo.
     *
     * @return bool Retorna true se o arquivo foi removido com sucesso, false caso contrário.
     */
    public function apagar(): bool
    {
        return unlink($this->path);
    }

    /**
     * Retorna a configuração do tempo de cache do arquivo representado por esta instância de Arquivo.
     *
     * @return string A string de configuração de cache a ser usada para o arquivo.
     */
    public function tempoCache(): string
    {
        $cache = Config::getConfigCache();

        if (!$cache["ativo"] || isset($cache["excluir"][$this->getExt()])) {
            return "no-cache";
        }

        if (isset($cache["incluir"][$this->getExt()])) {
            return "private, max-age=" . $cache['incluir'][$this->getExt()] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
        }

        return "private, max-age=" . $cache['default'] * 60 * 60 . ", stale-while-revalidate=" . $cache["revalidar"];
    }

    /**
     * Retorna o MIME type do arquivo.
     *
     * @return string O MIME type do arquivo.
     */
    public function getMimeType(): string
    {
        if (!$this->existe()) {
            return "text/plain";
        }

        switch ($this->getExt()) {
            case "js":
                return "application/javascript";
            case "css":
                return "text/css";
            default:
                return mime_content_type($this->path);
        }
    }

    /**
     * Obtém a extensão do arquivo.
     *
     * @return string Retorna a extensão do arquivo.
     */
    public function getExt(): string
    {
        $pathParts = pathinfo($this->path);
        return $pathParts['extension'] ?? '';
    }

    /**
     * Obtém o nome do arquivo.
     *
     * @return string Retorna o nome do arquivo.
     */
    public function getNome(): string
    {
        $pathParts = pathinfo($this->path);
        return $pathParts['basename'] ?? '';
    }

    /**
     * Obtém a string contendo o caminho até o arquivo ou um array com as pastas até o arquivo.
     *
     * @param bool $array Se verdadeiro, retorna um array das pastas.
     * @return string|array Retorna a string com as pastas até o arquivo ou um array das pastas.
     */
    public function getPasta(bool $array = false): string|array
    {
        $caminho = str_replace($this->getNome(), '', $this->path);
        if ($array) {
            $caminho = explode('/', $caminho);
        }
        return $caminho;
    }

    // ===== Funções auxiliares =================================================

    /**
     * Lê o conteúdo de um arquivo.
     *
     * @return string O conteúdo do arquivo.
     */
    private function lerArquivo(): string
    {
        return file_get_contents($this->path);
    }

    /**
     * Escreve no arquivo especificado.
     *
     * @param string $conteudo O conteúdo a ser escrito no arquivo.
     * @return bool Retorna true se a escrita for bem-sucedida, caso contrário, false.
     */
    private function escreverArquivo(string $conteudo): bool
    {
        return file_put_contents($this->path, $conteudo);
    }

    /**
     * Lê e decodifica o conteúdo de um arquivo JSON.
     *
     * @return array|null Retorna o array com os dados decodificados do arquivo JSON ou null se falhar.
     */
    private function lerJson(): ?array
    {
        $conteudo = $this->lerArquivo();
        return json_decode($conteudo, true);
    }

    /**
     * Escreve um array em um arquivo JSON.
     *
     * @param array $arr Os dados a serem convertidos e escritos no arquivo JSON.
     * @return bool Retorna true se a escrita for bem-sucedida, caso contrário, false.
     */
    private function escreverJson(array $arr): bool
    {
        $conteudo = is_array($arr) ? json_encode($arr) : $arr;
        return $this->escreverArquivo($conteudo);
    }

    /**
     * Cria as pastas necessárias até o arquivo especificado.
     *
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
