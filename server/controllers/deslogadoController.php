<?php

function cadastro($var = 'vazia', $var2, $var3)
{
    return [
        'parametro' => [
            $var,
            $var2,
            $var3
        ],
        'post' => $_POST,
        'arquivo' => $_FILES
    ];
}
