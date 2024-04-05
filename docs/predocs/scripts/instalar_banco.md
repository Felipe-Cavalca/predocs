# Instalar banco

A pasta `database/structure` contem varios scripts para a instação do banco de dados. Porém não é necessários roda-los manualmente.

Ao instalar o sistema pelo script `install.sh` ele já irá executar a execução dos arquivos necessários.

Porém caso esteja em ambiente de desenvolvimento e queira executar os scripts manualmente é possivel através do comando:

```shell
docker-compose exec predocs bash scripts/install_db.sh
```
