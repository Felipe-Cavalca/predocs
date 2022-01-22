<?php

    /**
     * Efetua a conexão com o banco de dados
     *
     * @return pdo com a conexão com o banco
     */
    function conexao(){

        global $_HostBanco;
        global $_PortaBanco;
        global $_NomeBanco;
        global $_UsuarioBanco;
        global $_SenhaBanco;

        try{
            $pdo = new PDO("mysql:host=$_HostBanco:$_PortaBanco;dbname=$_NomeBanco", $_UsuarioBanco, $_SenhaBanco);
            return $pdo;
        }catch(Exception $e){
            return false;
        }
    }

    /**
     * Função para inserir no banco
     * não é necessario fazer a conexão, a função já faz
     *
     * @param [type] $dados array associativo ['campo' => 'valor', 'campo2' => 'valor2', ...]
     * @param [type] $tabela nome da tabela
     * @return array
     */
    function insert($dados = [], $tabela = ''){
        try{
            //verifica se os dados ou a tabela estão vazios
            if(empty($dados)){
                throw new Exception('Não ha dados para inserir');
            }
            if(empty($tabela)){
                throw new Exception('Nenhuma tabela definida para inserir os dados');
            }

            //cria os campos de criado e modificado
            $dados['created'] = date('Y-m-d H:m:s');
            $dados['modified'] = date('Y-m-d H:m:s');

            //transfomra o array associativo em script sql
            $campos = implode(', ', array_Keys($dados));
            $valores = ':' . implode(', :', array_keys($dados));
            $Create = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

            //faz a conexao
            $pdo = conexao();

            //verifica se a conexão foi feita
            if(!$pdo){
                throw new Exception('A conexão não foi estabelecida');
            }

            //prepara o script
            $sth = $pdo->prepare($Create);

            //faz o insert
            if($sth->execute($dados)){
                return ['status' => true, 'id' => $pdo->lastInsertId()];
            }else{
                throw new Exception('O dado não foi inserido');
            }
        }catch(Exception $e){
            return ['status' => false];
        }

    }

    /**
     * Função para pesquisar no banco de dados
     * indices do array:
     * tabela - qual tabela será pesquisada
     * campos - os campos que são pesquisados
     * igual - pesquisa os iguais ['indice' => 'valor', .....]
     * contar - true para contar a quantidade de registros na tabela
     *
     * @param array $arr
     * @return array
     */
    function select($arr= []){

        try{
            $query = 'SELECT ';

            if(isset($arr['campos'])){
                foreach($arr['campos'] as $campo){
                    $query.= '`'.$campo.'`, ';
                }
                $query = rtrim($query, ', ');
                $query.= ' ';
            }else{
                $query.= "* ";
            }

            if(isset($arr['tabela'])){
                $query.= "FROM `".$arr['tabela'].'` ';
            }else{
                throw new Exception('Nenhuma tabela definida para a seleção');
            }

            if(isset($arr['igual'])){
                $query.= "WHERE ";

                foreach($arr['igual'] as $campo => $valor){
                    $query.= '`'.$campo.'` = "'.$valor. '" AND ';
                }

                $query = rtrim($query, ' AND');
            }

            $query = rtrim($query, ' ');

            $query .= ';';

            $conn = conexao();
            if(!$conn){
                throw new Exception('A conexão não foi estabelecida');
            }

            $execucao = $conn->prepare($query);
            $execucao->execute();

            if(isset($arr['contar']) && $arr['contar']){
                $retorno = $execucao->rowCount();
            }else{
                $retorno = [];
                foreach ($execucao as $res) {
                    $retorno[] = $res;
                }
            }

            return ['status' => true, 'retorno' => $retorno];
        }catch(Exception $e){
            return ['status' => false];
        }
    }

    /**
     * executa uma query sql
     *
     * @param string $query
     * @return array
     * status - scuesso na query ou não
     * retorno - retorno da query
     */
    function query($query) {
        try{
            $conn = conexao();
            if(!$conn){
                throw new Exception('A conexão não foi estabelecida');
            }
            $execucao = $conn->prepare($query);
            $execucao->execute();

            $retorno = [];
            foreach ($execucao as $res) {
                $retorno[] = $res;
            }

            return ['status' => true, 'retorno' => $retorno];
        }catch(Exception $e){
            return ['status' => false];
        }
    }
?>