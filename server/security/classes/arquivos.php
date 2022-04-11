<?php

class Arquivos
{
    /**
     * Função para pegar o conteudo do arquivo
     *
     * @param string $arquivo - caminho até o arquivo
     * @return string - conteudo do arquivo
     */
    public function ler($arquivo)
    {
        // Cria o recurso (abrir o arquivo)
        $handle = fopen($arquivo, "r");
        // Lê o arquivo
        $conteudo = fread($handle, filesize($arquivo));
        // Fecha o arquivo
        fclose($handle);
        return $conteudo;
    }

    /**
     * Função para gravar dados num arquivos
     *
     * @param string $arquivo
     * @param string $conteudo
     * @return void
     */
    public function gravar($arquivo, $conteudo)
    {
        //criamos o arquivo
        $arquivo = fopen($arquivo, "w");
        //verificamos se foi criado
        if ($arquivo == false) die("Não foi possível criar o arquivo.");
        //escrevemos no arquivo
        fwrite($arquivo, $conteudo);
        //Fechamos o arquivo após escrever nele
        fclose($arquivo);
    }

    /**
     * Função para listar os arquivos de uma pasta
     * 
     * @param string $path - caminho da lista de pastas 
     * @return arr - arry com os nomes dos arquivos/pastas de dentro do diretorio 
     */
    public function listar($path = '/')
    {
        $diretorio = dir($path);

        $arquivos = [];
        while ($arquivo = $diretorio->read()) {
            $arquivos[] = $arquivo;
        }
        $diretorio->close();

        return $arquivos;
    }

    /**
     * Função para ler um arquivo json
     * 
     * @param string $caminho - caminho até o json
     * @return arr - json decodificado
     */
    public function getJson($caminho)
    {
        $json = file_get_contents($caminho);
        return json_decode($json);
    }
}
