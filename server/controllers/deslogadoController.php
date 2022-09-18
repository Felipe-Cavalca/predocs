<?php

function cadastro($var = 'vazia')
{
    return [
        'parametro' => $var,
        'post' => $_POST,
        'arquivo' => $_FILES
    ];
}
