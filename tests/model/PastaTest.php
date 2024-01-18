<?php

use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/../../server/model/Pasta.php";

class PastaTest extends TestCase
{
    private string $pastaPrincipal = "pastaTmp";
    private string $subpasta = "subpasta";
    private string $pastaTeste = "pastaTmp/subpasta";

    private function limparDiretorios()
    {
        if (is_dir($this->pastaTeste)) {
            rmdir($this->pastaTeste);
        }
        if (is_dir($this->pastaPrincipal)) {
            rmdir($this->pastaPrincipal);
        }
    }

    public function testCriarPasta()
    {
        $pasta = new Pasta($this->pastaTeste);
        $this->assertInstanceOf(Pasta::class, $pasta);
    }

    public function testCriarPastaComCaminhoVazio()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("PredocsErro - O caminho da pasta não pode ser vazio.");

        $pasta = new Pasta("");
    }

    public function testCriarPastaComCaminhoInvalido()
    {
        $this->expectException(Error::class);

        $pasta = new Pasta([]);
    }

    public function testCriarPastaComCriacaoDePasta()
    {
        $pasta = new Pasta($this->pastaTeste, true);
        $this->assertInstanceOf(Pasta::class, $pasta);
        $this->assertDirectoryExists($this->pastaTeste);

        $this->limparDiretorios();
    }

    public function testCriarPastaTemporaria()
    {
        $pasta = new Pasta($this->pastaTeste, true, true);
        $this->assertInstanceOf(Pasta::class, $pasta);
        $this->assertDirectoryExists($this->pastaTeste);
        unset($pasta);
        $this->assertDirectoryDoesNotExist($this->pastaTeste);

        $this->limparDiretorios();
    }

    public function testGetCaminho()
    {
        $pasta = new Pasta($this->pastaTeste);
        $this->assertEquals($this->pastaTeste, $pasta->caminho);
    }

    public function testSetCaminho()
    {
        $pastaTemp2 = "pastaTmp2";

        $pasta = new Pasta($this->pastaTeste, false, true);
        $this->assertEquals($this->pastaTeste, $pasta->caminho);

        $pasta->caminho = $pastaTemp2;
        $this->assertEquals($pastaTemp2, $pasta->caminho);

        $pasta->caminho = $this->pastaTeste;
        $this->assertEquals($this->pastaTeste, $pasta->caminho);
    }

    public function testSetCaminhoComCaminhoVazio()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("PredocsErro - O caminho da pasta não pode ser vazio.");

        $pasta = new Pasta($this->pastaTeste);
        $pasta->caminho = "";
    }

    public function testSetCaminhoComCaminhoInvalido()
    {
        $this->expectException(Error::class);

        $pasta = new Pasta($this->pastaTeste);
        $pasta->caminho = [];
    }

    public function testGetNome()
    {
        $pasta = new Pasta($this->pastaTeste);
        $this->assertEquals($this->subpasta, $pasta->nome);
    }

    public function testSetNomeErro()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage("PredocsErro - Propriedade nome não pode ser definida.");

        $pasta = new Pasta($this->pastaTeste);
        $pasta->nome = "teste";
    }

    public function testGetTemporaria()
    {
        $pasta = new Pasta($this->pastaTeste, false, true);
        $this->assertTrue(true, $pasta->temporaria);
    }

    public function testSetTemporaria()
    {
        $pasta = new Pasta($this->pastaTeste, false, false);
        $this->assertFalse($pasta->temporaria);

        $pasta->temporaria = true;
        $this->assertTrue($pasta->temporaria);

        $pasta->temporaria = false;
        $this->assertFalse($pasta->temporaria);
    }

    public function testListarArquivos()
    {
        // Create a text file
        $conteudoArquivo = "This is the content of the text file.";
        $nomeArquivo = "example.txt";
        $filePath = $this->pastaTeste . "/" . $nomeArquivo;

        mkdir($this->pastaTeste, 0777, true);
        file_put_contents($filePath, $conteudoArquivo);

        $listaArquivos = Pasta::listarArquivos($this->pastaTeste);
        $this->assertIsArray($listaArquivos);
        $this->assertContains($nomeArquivo, $listaArquivos);

        unlink($filePath);
        $this->limparDiretorios();
    }

    public function testListarArquivosComPastaInexistente()
    {
        $listaArquivos = Pasta::listarArquivos($this->pastaTeste);
        $this->assertIsArray($listaArquivos);
    }

    public function testListarArquivosRecursivo()
    {
        $pasta = new Pasta($this->pastaTeste);
        $pasta->criar();

        // Criar subdiretórios
        $subpasta1 = $this->pastaTeste . "/subpasta1";
        $subpasta2 = $this->pastaTeste . "/subpasta2";
        mkdir($subpasta1, 0777, true);
        mkdir($subpasta2, 0777, true);

        // Criar arquivos nos subdiretórios
        $conteudoArquivo1 = "Este é o conteúdo do arquivo de texto 1.";
        $nomeArquivo1 = "exemplo1.txt";
        $filePath1 = $subpasta1 . "/" . $nomeArquivo1;
        file_put_contents($filePath1, $conteudoArquivo1);

        $conteudoArquivo2 = "Este é o conteúdo do arquivo de texto 2.";
        $nomeArquivo2 = "exemplo2.txt";
        $filePath2 = $subpasta2 . "/" . $nomeArquivo2;
        file_put_contents($filePath2, $conteudoArquivo2);

        $listaArquivos = Pasta::listarArquivosRecursivo($this->pastaTeste);

        $this->assertIsArray($listaArquivos);
        $this->assertContains($filePath1, $listaArquivos);
        $this->assertContains($filePath2, $listaArquivos);

        // Limpar
        unlink($filePath1);
        unlink($filePath2);
        rmdir($subpasta1);
        rmdir($subpasta2);
        $pasta->excluir();
    }

    public function testListarArquivosRecursivoComPastaInexistente()
    {
        $pastaInexistente = "pastaInexistente";
        $listaArquivos = Pasta::listarArquivosRecursivo($pastaInexistente);

        $this->assertIsArray($listaArquivos);
        $this->assertEmpty($listaArquivos);
    }
}
