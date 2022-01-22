<?php

/**
 * Função para printar algo na tela
 *
 * @param all $data
 * @return string
 */
function pr($data){
    echo '<pre>'.print_r($data, true).'</pre>';
}