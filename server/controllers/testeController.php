<?php

function aa(){
    $banco = new Banco();
    pr($banco->query("show tables"));
}
