<?php

class install
{
    //veriavel q guarda a url até o sistema
    public $url;

    //variavel q guarda o caminho apartir de / na url
    public $caminho;

    /**
     * Função contrutora do objeto install
     * @version 2
     * @access public
     */
    public function __construct()
    {
        $protocolo = ($_SERVER["SERVER_PORT"] == 443 ? "https" : "http");
        $host = "$protocolo://{$_SERVER["HTTP_HOST"]}";

        //paga o caminho apartir de /
        $this->caminho = explode("server/lis", $_SERVER["REDIRECT_URL"])[0];

        //pega a url que o sistema está rodando
        $this->url = $host . $this->caminho;
    }

    /**
     * Função para realizar a instalação do framework em uma pasta
     * @version 2.1.0
     * @access public
     * @return string mensagem
     */
    public function index($cache = "false"): string
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

            $vars = ["server" => $this->url . "server/", "caminho" => $this->caminho];

            $config->escrever(array_merge($original->ler(), $vars));
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

        $etapas["listaArquivosApp"] = function ($cache) {
            $config = new config;
            $funcoes = new funcoes;

            $caminho = $config->getCaminho("app");

            $arquivo = new Arquivo("{$caminho}/sw.js", true);
            $modelo = new Arquivo("{$caminho}/models/sw.js");

            if ($cache == "true") {
                $arquivos = $funcoes->listarArquivosRecursivos($caminho);
                $arquivos = str_replace("./../", $this->caminho, $arquivos);
                $arquivos = json_encode($arquivos, true);

                $arquivo->escrever(str_replace('"___ARRAY_DE_ARQUIVOS_AQUI___"', $arquivos, $modelo->ler()));
            } else {
                $arquivo->escrever(str_replace('"___ARRAY_DE_ARQUIVOS_AQUI___"', "[]", $modelo->ler()));
            }
        };

        foreach ($etapas as $etapa) {
            $etapa($cache);
        }

        return "Todas as etapas foram executadas, para mais informações veja o log";
    }
}
