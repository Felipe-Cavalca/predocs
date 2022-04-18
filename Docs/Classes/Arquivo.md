# Propriedades da classe Arquivo

## Construir um novo arquivo
```
$meuArquivo = new Arquivo("path/to/file.txt");
```
o retorno da função podera ser _true_ ou _false_

## ler conteudo de arquivo
```
$textoDoArquivo = $meuArquivo->ler();
```
O retorno padrão sera uma _string_
### exceção
Caso seja um arquivo .json o retorno será um _array_
