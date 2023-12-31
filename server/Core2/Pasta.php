<?php

class Pasta{

    public $caminho;

    /**
     * Construtor da classe Pasta.
     *
     * @param string $caminho O caminho da pasta.
     */
    public function __construct($caminho) {
        $this->caminho = $caminho;
    }

    /**
     * Retorna o caminho da pasta.
     *
     * @param string|null $pasta Opcional. O caminho da pasta. Se não for fornecido, retorna o caminho padrão.
     * @return string O caminho da pasta.
     */
    private static function getPath($pasta = null): string{
        return $pasta === null ? static::$caminho : $pasta;
    }

    /**
     * Retorna uma lista de conteudo em uma pasta específica.
     *
     * @param string|null $pasta O caminho da pasta. Se não for fornecido, a pasta atual será usada.
     * @return array A lista de arquivos e pastas na pasta.
     */
    public static function listar($pasta = null): array{
        $arquivos = scandir(static::getPath($pasta));
        $arquivos = array_diff($arquivos, ['.', '..']);

        return $arquivos;
    }

    /**
     * Retorna um array com a lista de arquivos e pastas de forma recursiva a partir de uma pasta.
     *
     * @param string|null $pasta O caminho da pasta. Se não for fornecido, será usada a pasta padrão.
     * @return array A lista de arquivos e pastas encontrados.
     */
    public static function listarRecursivo($pasta = null): array{
        $pasta = static::getPath($pasta);

        if (empty($pasta) || !is_dir($pasta)) {
            return [];
        }

        $arquivos = glob($pasta . '/**/*', GLOB_BRACE);
        $arquivos = array_filter($arquivos, 'is_file');
        shuffle($arquivos);

        return array_values($arquivos);
    }

}
