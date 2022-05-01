<?php

function validarPostLogin()
{
    if (isset($_POST)) {
        $config = new Config;
        $credenciais = $config->getConfigAdmin()["credenciais"];

        if ($_POST["login"] == $credenciais["login"] && $_POST["senha"] == $credenciais["senha"]) {
            return "logou";
        } else {
            return "invalido";
        }
    } else {
        return "view";
    }
}
