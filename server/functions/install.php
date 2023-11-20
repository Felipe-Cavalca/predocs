<?php

class Install
{
    public $url;
    public $caminho;

    /**
     * Função construtora da instância da classe.
     * Inicializa os caminhos e URLs apropriados.
     */
    public function __construct()
    {
        $protocolo = ($_SERVER["SERVER_PORT"] == 443 ? "https" : "http");
        $host = "$protocolo://{$_SERVER["HTTP_HOST"]}";

        //paga o caminho apartir de /
        $this->caminho = explode("server/predocs", $_SERVER["REDIRECT_URL"])[0];

        //pega a url que o sistema está rodando
        $this->url = $host . $this->caminho;
    }

    /**
     * Função para realizar a instalação do framework em uma pasta
     * @return string mensagem
     */
    public function index(): string
    {
        Log::registrar("iniciando Instalação");

        $this->copiaConfigApp();
        $this->editaConfigApp();
        $this->editaManifest();
        $this->instalaBanco();

        Log::registrar("Instalação Finalizada");

        return "Todas as etapas foram executadas, para mais informações veja o log";
    }

    /**
     * Copia os arquivos de configuração do diretório de modelo para o diretório de configuração do aplicativo.
     *
     * Esta função verifica e copia cada arquivo de configuração do diretório original
     * para o diretório de configuração do aplicativo, mantendo a estrutura de arquivos.
     *
     * @throws Exception Se ocorrer um erro durante o processo de cópia.
     */
    private function copiaConfigApp(): void
    {
        try {
            Log::registrar("Copiando config do app");
            // Define os caminhos para os diretórios originais e de destino
            $caminhoOriginal = Config::getCaminho("app") . "/models/config/";
            $caminhoConfig = Config::getCaminho("app") . "/config/";

            // Obtém a lista de arquivos do diretório original
            $arquivosOriginais = Funcoes::listarArquivos($caminhoOriginal);

            // Verifica e copia cada arquivo para o diretório de configuração
            foreach ($arquivosOriginais as $nomeArquivo) {
                // Ignora os diretórios especiais '.' e '..'
                if ($nomeArquivo === "." || $nomeArquivo === "..") {
                    continue;
                }

                // Cria instâncias de Arquivo para origem e destino
                $arquivoOrigem = new Arquivo($caminhoOriginal . $nomeArquivo);

                // Copia o arquivo de origem para o diretório de configuração
                $arquivoOrigem->copiar($caminhoConfig . $nomeArquivo);
            }
        } catch (Exception $e) {
            Log::registrar("Erro ao copiar arquivos de configuração: " . $e->getMessage(), "Install", "copiaConfig");
        }
    }

    /**
     * Edita o arquivo de configuração do aplicativo.
     *
     * Esta função realiza a edição do arquivo de configuração do aplicativo,
     * adicionando novas variáveis ou atualizando as existentes com valores específicos.
     *
     * @throws Exception Se ocorrer um erro durante o processo de edição do arquivo.
     */
    private function editaConfigApp(): void
    {
        try {
            Log::registrar("Editando configurações do app");
            // Define os caminhos para o arquivo original e de destino
            $caminhoOriginal = Config::getCaminho("app") . "/models/config/app.json";
            $caminhoConfig = Config::getCaminho("app") . "/config/app.json";

            // Cria instâncias de Arquivo para origem e destino com permissão de escrita
            $arquivoOriginal = new Arquivo($caminhoOriginal);
            $arquivoConfig = new Arquivo($caminhoConfig, true);

            // Define as novas variáveis a serem adicionadas ou atualizadas
            $novasVariaveis = [
                "server" => $this->url . "server/",
                "caminho" => $this->caminho
            ];

            // Lê o conteúdo do arquivo original e mescla com as novas variáveis
            $conteudoOriginal = $arquivoOriginal->ler();
            $novoConteudo = array_merge($conteudoOriginal, $novasVariaveis);

            // Escreve o novo conteúdo no arquivo de configuração do aplicativo
            $arquivoConfig->escrever($novoConteudo);
        } catch (Exception $e) {
            Log::registrar("Erro ao editar o arquivo de configuração: " . $e->getMessage(), "Install", "editaConfigApp");
        }
    }

    /**
     * Edita o arquivo de manifesto do aplicativo.
     *
     * Esta função realiza a edição do arquivo de manifesto do aplicativo,
     * inserindo ou atualizando valores específicos como a URL de início e atalhos.
     *
     * @throws Exception Se ocorrer um erro durante o processo de edição do arquivo.
     */
    private function editaManifest(): void
    {
        try {
            Log::registrar("Editando manifesto");
            // Define os caminhos para o arquivo original e de destino
            $caminhoOriginal = Config::getCaminho("app") . "/models/config/manifest.json";
            $caminhoConfig = Config::getCaminho("app") . "/config/manifest.json";

            // Cria instâncias de Arquivo para origem e destino com permissão de escrita
            $arquivoOriginal = new Arquivo($caminhoOriginal);
            $arquivoConfig = new Arquivo($caminhoConfig, true);

            // Define as novas configurações a serem adicionadas ou atualizadas
            $novasConfig = [
                "start_url" => $this->url . "app/pages/",
                "shortcuts" => [
                    0 => [
                        "name" => "Inicio",
                        "url" => $this->url . "app/pages/"
                    ]
                ]
            ];

            // Lê o conteúdo do arquivo original e mescla com as novas configurações
            $conteudoOriginal = $arquivoOriginal->ler();
            $novoConteudo = array_merge($conteudoOriginal, $novasConfig);

            // Escreve o novo conteúdo no arquivo de manifesto do aplicativo
            $arquivoConfig->escrever($novoConteudo);
        } catch (Exception $e) {
            Log::registrar("Erro ao editar o arquivo de manifesto: " . $e->getMessage(), "Install", "editaManifest");
        }
    }

    /**
     * Instalação do banco de dados.
     *
     * Esta função realiza a instalação do banco de dados por meio do controlador "autorun",
     * chamando o método "installBanco()" para executar o processo de instalação do banco de dados.
     *
     * @throws Exception Se ocorrer um erro durante a instalação do banco de dados.
     */
    private function instalaBanco(): void
    {
        try {
            Log::registrar("Instalando banco");
            // Inclui o controlador "autorun" para execução da instalação do banco de dados
            $autoRun = Funcoes::incluiController("autorun", true);

            // Chama o método "installBanco()" no controlador "autorun" para instalar o banco de dados
            $autoRun->installBanco();
        } catch (Exception $e) {
            Log::registrar("Erro durante a instalação do banco de dados: " . $e->getMessage(), "Install", "instalaBanco");
        }
    }
}
