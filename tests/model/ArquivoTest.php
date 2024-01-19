<?php

use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/../../server/model/Arquivo.php";

class ArquivoTest extends TestCase
{
    private string $arquivo = "arquivo.txt";

    public function testCriarArquivo()
    {
        $arquivoObj = new Arquivo($this->arquivo);
        $this->assertInstanceOf(Arquivo::class, $arquivoObj);
    }

    public function testCriarArquivoComCaminhoVazio()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage("PredocsErro - O caminho do arquivo deve ser uma string válida.");

        $arquivo = new Arquivo("");
    }

    public function testCriarArquivoComCaminhoInvalido()
    {
        $this->expectException(Error::class);

        $arquivo = new Arquivo([]);
    }

    public function testCriarArquivoComCriacaoDeArquivo()
    {
        $arquivoObj = new Arquivo($this->arquivo, true);
        $this->assertInstanceOf(Arquivo::class, $arquivoObj);
        $this->assertFileExists($this->arquivo);

        if (is_file($this->arquivo)) {
            unlink($this->arquivo);
        }
    }

    public function testCriarArquivoTemporario()
    {
        $arquivoObj = new Arquivo($this->arquivo, true, true);
        $this->assertInstanceOf(Arquivo::class, $arquivoObj);
        $this->assertFileExists($this->arquivo);
        unset($arquivoObj);
        $this->assertFileDoesNotExist($this->arquivo);

        if (is_file($this->arquivo)) {
            unlink($this->arquivo);
        }
    }

    public function testGetNome()
    {
        $arquivoObj = new Arquivo($this->arquivo);
        $this->assertEquals($this->arquivo, $arquivoObj->nome);
    }

    public function testSetNome(){
        $this->expectException(Error::class);

        $arquivoObj = new Arquivo($this->arquivo);
        $arquivoObj->nome = "novoNome.txt";
    }

    public function testSetCaminho()
    {
        $arquivoObj = new Arquivo($this->arquivo);
        $arquivoObj->caminho = "pasta/tes.log";
        $this->assertEquals("pasta", $arquivoObj->caminho);
    }

    public function testSetCaminhoComCaminhoVazio()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage("PredocsErro - O caminho do arquivo deve ser uma string válida.");

        $arquivoObj = new Arquivo($this->arquivo);
        $arquivoObj->caminho = "";
    }

    public function testGetExtensao()
    {
        $arquivoObj = new Arquivo($this->arquivo);
        $this->assertEquals("txt", $arquivoObj->extensao);
    }
}
