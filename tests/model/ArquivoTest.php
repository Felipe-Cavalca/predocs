<?php

use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/../../server/model/Arquivo.php";

class ArquivoTest extends TestCase
{
    public function testArquivo()
    {
        $arquivo = new Arquivo("nome", );
        $arquivo->setNome("teste");
        $arquivo->setTipo("pdf");
        $arquivo->setTamanho(100);
        $arquivo->setConteudo("conteudo");

        $this->assertEquals("teste", $arquivo->getNome());
        $this->assertEquals("pdf", $arquivo->getTipo());
        $this->assertEquals(100, $arquivo->getTamanho());
        $this->assertEquals("conteudo", $arquivo->getConteudo());
    }
}
