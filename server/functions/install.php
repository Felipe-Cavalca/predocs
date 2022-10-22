<?php

class install
{
    //veriavel q guarda a url até o sistema
    public $url;

    public function __construct()
    {
        //pega a url que o sistema está rodando
        $this->url = ($_SERVER["SERVER_PORT"] == 443 ? "https" : "http") .
            "://{$_SERVER["HTTP_HOST"]}" .
            explode("server/lis", $_SERVER["REDIRECT_URL"])[0];
    }

    /**
     * Função para realizar a instalação do framework em uma pasta
     * @version 1
     * @access public
     * @return string mensagem
     */
    public function index(): string
    {
        $etapas = [];
        //declara funções para instalação

        $etapas["copiaConfigApp"] = function () {
            $config = new Config;
            $funcoes = new funcoes;
            $caminhoOriginal = "{$config->getCaminho("app")}/models/config/";
            $caminhoConfig = "{$config->getCaminho("app")}/config/";
            foreach ($funcoes->listarArquivos($caminhoOriginal) as $nome) {
                if ($nome == "." || $nome == "..")
                    continue;

                $arquivo = new Arquivo($caminhoOriginal . $nome);
                $arquivo->copiar($caminhoConfig . $nome);
            }
        };

        $etapas["editaApp"] = function () {
            $config = new config;

            $original = new Arquivo("{$config->getCaminho("app")}/models/config/app.json");

            $config = new Arquivo("{$config->getCaminho("app")}/config/app.json", true);

            $config->escrever(array_merge($original->ler(), ["server" => $this->url . "server/"]));
        };

        $etapas["editaManifest"] = function () {
            $config = new config;

            $original = new Arquivo("{$config->getCaminho("app")}/models/config/manifest.json");

            $config = new Arquivo("{$config->getCaminho("app")}/config/manifest.json", true);

            $novasConfig = [
                "start_url" => $this->url . "app/pages/",
                "shortcuts" => [
                    0 => [
                        "name" => "Inicio",
                        "url" => $this->url . "app/pages/"
                    ]
                ]
            ];

            $config->escrever(array_merge($original->ler(), $novasConfig));
        };

        $etapas["instalaBanco"] = function () {
            $funcoes = new funcoes;

            $autoRun = $funcoes->incluiController("autorun", true);
            $autoRun->installBanco();
        };

        foreach ($etapas as $etapa) {
            $etapa();
        }

        return "Todas as etapas foram executadas, para mais informações veja o log";
    }
}
