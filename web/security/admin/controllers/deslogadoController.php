<?php

/**
 * Valida os dados do post de login da tela administrativa do framework
 */
function validarPostLogin()
{
    if (isset($_POST["login"]) && isset($_POST["senha"])) {
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
