<?php

namespace Predocs\Include;

/**
 * Acesso Propriedades Por Função
 *
 * Permite que as propriedades de uma classe sejam acessadas por funções
 *
 * @package Predocs\Include
 * @author Felipe dos S. Cavalca
 * @version 1.0.0
 * @since 1.0.0
 */
trait acessoPropriedadesPorFuncao
{
    public function __get($propriedade): mixed
    {
        $propriedade = ucfirst($propriedade);
        $nomeFuncao = "get{$propriedade}";

        if (method_exists($this, $nomeFuncao)) {
            return $this->$nomeFuncao();
        }

        return null;
    }
}
