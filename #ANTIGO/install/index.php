<?php

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install</title>
</head>
<body>
    <h1>Instalador lis</h1>
    <hr>

    <form action="instalar.php">
        <label for="url">URL</label><br>
        <input type="text" name="url" id="url" value=""><br>
        <label for="nome">Nome</label><br>
        <input type="text" name="nome" id="nome" value="Lis"><br>
        <label for="pasta">Pasta</label><br>
        <input type="text" name="pasta" id="pasta" value="..\htdocs\lis"><br>
        <br>
        <button>Instalar</button>
    </form>
</body>

<script type="text/javaScript">
    //setando url atual no campo url
    document.querySelector('input[name="url"').value = window.location.protocol + "//" + window.location.host + window.location.pathname;
</script>
</html>