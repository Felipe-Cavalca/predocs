#modelo de pagina

a pagina em sí não passa de arquivos html css e js com os frameworks que voçê desejar
```
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="core/css/index.css">
    <title>Login</title>
</head>

<body>
    <script type="text/javascript">
        const Lis = {
            'scripts' : [
                '{{domain}}js/pages/home.js'
            ],
            'init' : function (){
                iniciaPagina();
                log('teste');
            }
        }
    </script>
    <script type="module" src="core/js/index.js"></script>
</body>

</html>
```

para importar todos os css usados no projeto basta importar o arquivo localizado em ```web/core/css/index.css```

para importar os javascript do projeto basta importar o arquivo ```web/core/js/index.js```

####entendendo a inicialização
```
<script type="text/javascript">
    const Lis = {
        'scripts' : [
            '{{js}}pages/home.js'
        ],
        'styles' : [
            '{{css}}pages/home.css'
        ],
        'init' : function (){
            iniciaPagina();
            log('teste');
        }
    }
</script>
<script type="module" src="core/js/index.js"></script>
```

antes de se importar o ```web/core/js/index.js``` é necessario declarar uma contante ```Lis```. Ela recebera os seguintes parametros:
**scripts -** os scripts js que são importados nesta pagina
**styles -** caminhos para os css que serão carregados na pagina
**init -** função js que sera executada apos o carragamento de todos os js