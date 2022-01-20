# modelo de pagina

a pagina em sí não passa de arquivos html css e js com os frameworks que você desejar
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

    <!--Sua pagina aqui-->

    <script type="text/javascript">
        const Lis = {
            'scripts': [
                //strings de arquivos js da pagina
            ],
            'styles': [
                //strings de arquivos css da pagina
            ],
            'init': function () {
                //js a ser executado apos a inicialização
            }
        }
    </script>
    <script type="module" src="core/js/index.js"></script>
</body>

</html>
```

para importar todos os css usados no projeto basta importar o arquivo localizado em ```web/core/css/index.css```

para importar os javascript do projeto basta importar o arquivo ```web/core/js/index.js```

## entendendo a inicialização
```
<script type="text/javascript">
    const Lis = {
        'scripts': [
            //strings de arquivos js da pagina
        ],
        'styles': [
            //strings de arquivos css da pagina
        ],
        'init': function () {
            //js a ser executado apos a inicialização
        }
    }
</script>
<script type="module" src="core/js/index.js"></script>
```

antes de se importar o ```web/core/js/index.js``` é necessario declarar uma contante ```Lis```. Ela recebera os seguintes parametros:
**scripts -** os scripts js que são importados nesta pagina
**styles -** caminhos para os css que serão carregados na pagina
**init -** função js que sera executada apos o carragamento de todos os arquivos

#### scripts:
Para os scripts se pode declarar a url completa de arquivos ```"https://meu-arquivo.js"``` ou caso seja um arquivo dentro da pasta ```web/js``` pode se usar: ```"{{js}}arquivo.js"``` ou ```"{{js}}pasta/arquivo.js"```, onde a string ```{{js}}``` será substituida pelo caminho da pasta
caminho esse que pode ser editado em ```core/js/vars.js```

#### styles:
Para os css se pode declarar a url completa de arquivos ```"https://meu-arquivo.css"``` ou caso seja um arquivo dentro da pasta ```web/css``` pode se usar: ```"{{css}}arquivo.js"``` ou ```"{{css}}pasta/arquivo.css"```, onde a string ```{{css}}``` será substituida pelo caminho da pasta,
caminho esse que pode ser editado em ```core/js/vars.js```