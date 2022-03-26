<?php
$URLS['dominio'] = '../../';
$URLS['assets'] = $URLS['dominio'] . 'assets/';
$URLS['coreServer'] = './../security/core/';

include 'php/arquivos.php';

if($_POST){
    gravarArquivo($_POST['arquivo'], $_POST['conteudo']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?= $URLS['assets'] ?>styles/carregando.css" rel="stylesheet">
    <link href="styles/index.css" rel="stylesheet">
    <title>Admin</title>
</head>

<body>
    <section id="body" class="scale-transition scale-out" style="display:none">
        <p>Manutenção do framework</p>

        <form action="#" method="POST">
            <label for="variavies_core_server">Variaveis do core do servidor</label><br>
            <input type="hidden" name="arquivo" value="<?= $URLS['coreServer'] . 'vars.php' ?>">
            <textarea id="variavies_core_server" name="conteudo"><?= lerArquivo($URLS['coreServer'] . 'vars.php') ?></textarea>
            <button type="submit">Salvar</button>
        </form>

    </section>
    <carregando class="scale-transition scale-in">
        <img class="materialboxed" src="<?= $URLS['assets'] ?>img/Carregando.gif">
    </carregando>
    <script type="text/javascript" src="<?= $URLS['assets'] ?>scripts/carregando.js"></script>
    <script type="text/javascript" src="scripts/index.js"></script>
</body>

</html>
