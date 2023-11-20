<?php

/**
 * Funções para gerenciamento do framework
 */
class Funcoes
{

    /**
     *  ======= Funções do framework =======
     */

    /**
     * Analisa a requisição de um usuário e realiza o roteamento para os controllers e suas respectivas funções.
     *
     * @return mixed Retorna a saída da função chamada ou o código de status da resposta.
     */
    public static function init(): mixed
    {
        static::configPHP();
        static::post();
        static::get();
        $predocs = $_GET["controller"] == "predocs";

        static::get($predocs);

        if (empty($_GET["controller"])) {
            return static::returnStatusCode(404);
        }

        $controller = static::incluiController($_GET["controller"], $predocs);

        if (gettype($controller) === "integer") {
            switch ($controller) {
                case 404:
                    return static::returnStatusCode(404);
                case 200:
                    if (function_exists($_GET["function"])) {
                        return call_user_func($_GET["function"], $_GET["param1"], $_GET["param2"], $_GET["param3"]);
                    } else {
                        return static::returnStatusCode(404);
                    }
                default:
                    return static::returnStatusCode(500);
            }
        } elseif (gettype($controller) === "object") {
            if (method_exists($controller, $_GET["function"])) {
                return call_user_func([$controller, $_GET["function"]], $_GET["param1"], $_GET["param2"], $_GET["param3"]);
            } else {
                return static::returnStatusCode(404);
            }
        } else {
            return static::returnStatusCode(500);
        }
    }

    /**
     * Configura as definições do PHP, incluindo headers, caminhos de pastas e configurações de sessão.
     */
    private static function configPHP(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Content-Type: application/json");

        static::criarPasta(Config::getCaminho("storage"));
        static::criarPasta(Config::getCaminho("session"));

        session_save_path(Config::getCaminho("session"));

        ini_set("memory_limit", "5G");
        ini_set("display_errors", "1");

        session_start();
    }

    /**
     * Valida o tipo de requisição e atualiza a variável $_POST conforme necessário.
     */
    private static function post(): void
    {
        $json = json_decode(file_get_contents('php://input'), true);
        $_POST = (is_array($json) ? $json : $_POST);
    }

    /**
     * Organiza os parâmetros da URL e atualiza a variável $_GET com os valores obtidos.
     *
     * @param bool $predocs Define se a função pertence aos predocs.
     * @return void
     */
    private static function get(bool $predocs = false): void
    {
        $retorno = [
            "_Pagina" => $_GET["_Pagina"] ?? "",
        ];

        $url = explode("/", $retorno["_Pagina"]);
        $params = [
            "controller",
            "function",
            "param1",
            "param2",
            "param3"
        ];

        $count = ($predocs) ? 1 : 0;
        foreach ($params as $param) {
            if ($param == "function") {
                $retorno[$param] = empty($url[$count]) ? "index" : $url[$count];
            } else {
                $retorno[$param] = isset($url[$count]) ? $url[$count] : null;
            }
            $count++;
        }

        $_GET = $retorno;
    }

    /**
     * Retorna o código de status correspondente à resposta do erro.
     *
     * @param int $codigo Código da resposta de erro.
     * @return mixed Retorna a mensagem associada ao código de status do erro.
     */
    public static function returnStatusCode(int $codigo): mixed
    {
        $codes = Config::getConfig()["messageReturnStatusCode"];
        $codigo = array_key_exists($codigo, $codes) ? $codigo : 500;
        static::setStatusCode($codigo);
        return $codes[$codigo];
    }

    /**
     * Inclui um controller e retorna o objeto do controller se existir ou o status da importação.
     *
     * @param string $nomeController Nome do controller.
     * @param bool $predocs Indica se é uma função da predocs.
     * @return mixed Retorna o objeto para o controller se existir ou o status da importação.
     *               - Se não existir, retorna 404.
     *               - Se a classe não existir, retorna 200 e inclui o arquivo.
     */
    public static function incluiController(string $nomeController, bool $predocs = false): mixed
    {
        $controllerFilePath = $predocs ? Config::getCaminho("functions") . "/{$nomeController}.php" : Config::getCaminho("controller") . "/{$nomeController}Controller.php";
        $controller = new Arquivo($controllerFilePath);

        if (!$controller->existe()) {
            return 404;
        }

        $controller->renderiza();

        if (!class_exists($nomeController)) {
            return 200;
        }

        return new $nomeController();
    }

    /**
     *  ======= Funções do globais =======
     */

    /**
     * Define o código de status HTTP da resposta.
     *
     * @param int $code Código da resposta HTTP.
     * @return void
     */
    public static function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Lista os arquivos de um diretório específico.
     *
     * @param string $pasta Caminho do diretório (padrão é '/').
     * @return array Array com os nomes dos arquivos/pastas dentro do diretório.
     */
    public static function listarArquivos(string $pasta = '/'): array
    {
        $diretorio = dir($pasta);
        $arquivos = [];

        while ($arquivo = $diretorio->read()) {
            $arquivos[] = $arquivo;
        }

        $diretorio->close();
        return $arquivos;
    }

    /**
     * Lista os arquivos de forma recursiva a partir de um determinado diretório.
     *
     * @param string $pasta Caminho do diretório para listar os arquivos.
     * @return array Array com o nome dos arquivos do diretório.
     */
    public static function listarArquivosRecursivos(string $pasta): array
    {
        if (empty($pasta) || !is_dir($pasta)) {
            return [];
        }

        $scan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pasta));
        $arquivos = $retorno = [];

        foreach ($scan as $arquivo) {
            if (!$arquivo->isDir()) {
                $arquivos[] = $arquivo->getPathname();
            }
        }
        shuffle($arquivos);

        foreach ($arquivos as &$valor) {
            $retorno[] = $valor;
        }

        return $retorno;
    }

    /**
     * Verifica se a pasta existe e, se não existir, cria-a.
     *
     * @param string $path Caminho até a pasta.
     * @param int $permission Permissão da pasta (padrão é 0777).
     * @return bool Retorna true se a pasta já existir ou for criada com sucesso, senão false.
     */
    public static function criarPasta(string $path, int $permission = 0777): bool
    {
        // Verifica se a pasta não existe
        if (!is_dir($path)) {
            // Tenta criar a pasta com a permissão fornecida
            return mkdir($path, $permission, true);
        }
        // Se a pasta já existe, retorna true
        return true;
    }

    /**
     * Obtém as URLs da documentação com base nos parâmetros da requisição atual.
     *
     * @return array Retorna um array contendo as URLs para a documentação.
     */
    public static function getLinksDocs(): array
    {
        $urls = [];

        if (isset($_GET['controller']) && isset($_GET['function'])) {
            $function = $_GET['controller'] . "/" . $_GET['function'] . ".md";

            $docs = Config::getConfig()["docs"];

            if (isset($docs["web"], $docs["markdown"])) {
                $urls["web"] = $docs["web"] . $function;
                $urls["markdown"] = $docs["markdown"] . $function;
            }
        }

        return $urls;
    }
}

/**
 * Função para printar algo na tela
 * @version 1
 * @access public
 * @param mixed $data
 * @return void
 */
function pr(mixed $data): void
{
    echo '<pre>' . print_r($data, true) . '</pre>';
}
