<?php

use PHPUnit\Framework\TestCase;
include_once "server/Core2/Pasta.php";

class PastaTest extends TestCase
{
    public $nomePasta = "PhpTestFrameworkPredocs";
    public $arquivo1 = "arquivo1.txt";
    public $arquivo2 = "arquivo2.txt";
    public $pastaTemp;
    public $subPastaTemp;

    /**
     * Cria as pastas e arquivos necessários para o teste.
     */
    private function criaPastas()
    {
        // Cria uma pasta temporária para o teste
        $this->pastaTemp = sys_get_temp_dir() . '\\' . $this->nomePasta;
        mkdir($this->pastaTemp);

        // Cria alguns arquivos na pasta temporária
        touch($this->pastaTemp . '/' . $this->arquivo1);
        touch($this->pastaTemp . '/' . $this->arquivo2);

        // Cria uma subpasta na pasta temporária
        $this->subPastaTemp = $this->pastaTemp . '/subpasta';
        mkdir($this->subPastaTemp);

        // Cria alguns arquivos na subpasta
        touch($this->subPastaTemp . '/' . $this->arquivo1);
        touch($this->subPastaTemp . '/' . $this->arquivo2);
    }

    /**
     * Exclui as pastas temporárias e os arquivos nelas contidos.
     */
    private function excluiPastas()
    {
        // Limpa a subpasta temporária
        unlink($this->subPastaTemp . '/' . $this->arquivo1);
        unlink($this->subPastaTemp . '/' . $this->arquivo2);
        rmdir($this->subPastaTemp);

        // Limpa a pasta temporária
        unlink($this->pastaTemp . '/' . $this->arquivo1);
        unlink($this->pastaTemp . '/' . $this->arquivo2);
        rmdir($this->pastaTemp);
    }

    /**
     * Teste para verificar se a função listar retorna os arquivos corretos.
     */
    public function testListar()
    {
        // Cria as pastas e arquivos
        $this->criaPastas();

        // Chama a função listar
        $arquivos = Pasta::listar($this->pastaTemp);

        // Verifica se a função listar retornou os arquivos corretos
        $this->assertContains($this->arquivo1, $arquivos);
        $this->assertContains($this->arquivo2, $arquivos);

        // Exclui as pastas e arquivos
        $this->excluiPastas();
    }

    /**
     * Teste para listar os arquivos de forma recursiva.
     */
    public function testListarRecursivo()
    {
        // Cria as pastas e arquivos
        $this->criaPastas();

        // Chama a função listarRecursivo
        $arquivos = Pasta::listarRecursivo($this->pastaTemp);

        // Verifica se a função listarRecursivo retornou os arquivos corretos
        $this->assertContains($this->subPastaTemp . '/' . $this->arquivo1, $arquivos);
        $this->assertContains($this->subPastaTemp . '/' . $this->arquivo2, $arquivos);

        // Exclui as pastas e arquivos
        $this->excluiPastas();
    }
}
