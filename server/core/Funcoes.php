<?php

/**
 * Funções para gerenciamento do framework
 */
class funcoes
{

    /**
     *  ======= Funções do framework =======
     */

    /**
     * funcao para analizar a request de um usuario
     * @version 3.1.0
     * @access public
     * @return mixed
     */
    public function init(): mixed
    {
        $this->configPHP();
        $this->post();
        $this->get();
        $predocs = $_GET["controller"] == "predocs";

        $this->get(predocs: $predocs);

        if (empty($_GET["controller"])) return $this->returnStatusCode(404);

        $controller = $this->incluiController(nomeController: $_GET["controller"], predocs: $predocs);

        if (gettype($controller) === "integer") {
            switch ($controller) {
                case 404:
                    return $this->returnStatusCode(404);
                case 401:
                    return $this->returnStatusCode(401);
                case 200:
                    if (function_exists($_GET["function"])) {
                        return call_user_func($_GET["function"], $_GET["param1"], $_GET["param2"], $_GET["param3"]);
                    } else {
                        return $this->returnStatusCode(404);
                    }
                default:
                    return $this->returnStatusCode(500);
            }
        } elseif (gettype($controller) === "object") {
            if (method_exists($controller, $_GET["function"])) {
                return call_user_func([$controller, $_GET["function"]], $_GET["param1"], $_GET["param2"], $_GET["param3"]);
            } else {
                return $this->returnStatusCode(404);
            }
        } else {
            return $this->returnStatusCode(500);
        }
    }

    /**
     * Função para setar as config do php
     * @version 1.1
     * @access public
     * @return void
     */
    private function configPHP(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Content-Type: application/json");

        $config = new Config();
        $funcoes = new funcoes();
        $funcoes->criaPasta($config->getCaminho("storage"));
        $funcoes->criaPasta($config->getCaminho("session"));

        session_save_path($config->getCaminho("session"));

        ini_set("memory_limit", "5G");

        ini_set("display_errors", "1");

        session_start();

        return;
    }

    /**
     * Função para validar se os dados estão vindo via json ou form-encode
     * @version 1
     * @access public
     * @return void
     */
    private function post(): void
    {
        $json = json_decode(file_get_contents('php://input'), true);
        $_POST = (is_array($json) ? $json : $_POST);
        return;
    }

    /**
     * Função para organizar os dados do get
     * @version 3.2.1
     * @access public
     * @return void
     */
    private function get($predocs = false): void
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
            switch ($param) {
                case "function":
                    $retorno[$param] = empty($url[$count]) ? "index" : $url[$count];
                    break;
                default:
                    $retorno[$param] = isset($url[$count]) ? $url[$count] : null;
            }
            $count++;
        }

        $_GET = $retorno;
        return;
    }

    /**
     * Função para retornar o status code de erro
     * @version 1.1.0
     * @access public
     * @param int $codigo Codido da resposta
     * @return array
     */
    public function returnStatusCode($codigo)
    {
        $codes = [
            404 => ["status" => false, "msg" => "A função solicitada não foi encontrada"],
            401 => ["status" => false, "msg" => "Acesso negado"],
            500 => ["status" => false, "msg" => "Erro interno"]
        ];
        $codigo = array_key_exists($codigo, $codes) ? $codigo : 500;
        $this->setStatusCode($codigo);
        return $codes[$codigo];
    }

    /**
     * inclui um controller
     * @version 3.0.0
     * @access public
     * @param string $nome nome do controller
     * @param bool $predocs Função da predocs
     * @return object|int obj para o controller, int com o status da importação
     * caso haja uma classe, retorna o mesmo
     * caso não haja - true e inclui o arquivo
     */
    public function incluiController(string $nomeController, bool $predocs = false): mixed
    {
        $config = new Config;

        $controllerFilePath = $predocs ? "{$config->getCaminho("functions")}/{$nomeController}.php" : "{$config->getCaminho("controller")}/{$nomeController}Controller.php";
        $controller = new Arquivo($controllerFilePath);

        if (!$controller->existe()) {
            return 404;
        }

        $controller->renderiza();

        if (!class_exists($nomeController)) {
            return 200;
        }

        $obj = new $nomeController();

        if (method_exists($obj, "__autorizado") && !$obj->__autorizado($_GET["function"])) {
            return 401;
        }

        return $obj;
    }

    /**
     *  ======= Funções do globais =======
     */

    /**
     * Função para setar o status code de retorno
     * @version 1.0.0
     * @access public
     * @param int $code Codigo da resposta
     * @return void
     */
    public function setStatusCode($code)
    {
        http_response_code($code);
    }

    /**
     * Função para listar os arquivos de uma pasta
     * @version 1
     * @access public
     * @param string $pasta caminho da lista de pastas
     * @return array array com os nomes dos arquivos/pastas de dentro do diretorio
     */
    public function listarArquivos(string $pasta = '/'): array
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
     * Função para listar arquivos recursivamente de uma pasta
     * @version 1
     * @access public
     * @author https://gist.github.com/sistematico/08c5240f5c647cf3f650f395448c69e9
     * Modificado para o framework
     * @param string $pasta caminho da lista de arquivos
     * @return array array com o nome dos arquivos do diretorio
     */
    public function listarArquivosRecursivos(string $pasta): array
    {
        if (empty($pasta))
            return [];

        if (!is_dir($pasta))
            return [];

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
     * Valida se campos existem em um array
     * @version 2
     * @access public
     * @param array $indices nome dos campos a serem verificados
     * @param array $array array a ser verificado
     * @return array ["status" => bool, "msg" => string]
     */
    public function isset(array $indices = [], array $array = []): array
    {
        $array = $array ?: $_POST;
        $indicesFaltantes = array_diff($indices, array_keys($array));
        if (!empty($indicesFaltantes)) {
            return [
                "status" => false,
                "msg" => "Indices faltantes: " . implode(", ", $indicesFaltantes)
            ];
        }
        return [
            "status" => true,
            "msg" => "Todos os índices existem"
        ];
    }

    /**
     * Valida se indices de um array não estão vazios
     * @version 1.1.0
     * @access public
     * @param array $indices nome dos indices a serem verificados
     * @param array $array array a ser verificado
     * @return ["status" => bool, "msg" => string]
     */
    public function empty(array $indices = [], array $array = []): array
    {
        $array = $array ?: $_POST;
        foreach ($indices as $indice) {
            if (empty($array[$indice])) {
                return ["status" => true, "msg" => "Campo '{$indice}' está vazio"];
            }
        }

        return ["status" => false, "msg" => "Todos os campos estão ok"];
    }

    /**
     * Valida se uma pasta existe, caso não exista cria a mesma
     * @version 1
     * @access public
     * @param string $caminho - caminho até a pasta
     * @param int $permission - permissão da pasta
     * @return bool
     */
    public function criaPasta(string $path, int $permission = 0777): bool
    {
        if (!is_dir($path)) return mkdir($path, $permission, true);
        return false;
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
