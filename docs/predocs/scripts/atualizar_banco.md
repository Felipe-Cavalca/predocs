# Atualizar banco

A pasta `database/migration` contem varios scripts para a atualização do banco de dados. Porém não é necessários roda-los manualmente.

Ao executar o arquivo de atualização `update.sh` já irá executar a execução dos arquivos necessários.

Porém caso esteja em ambiente de desenvolvimento e queira executar os scripts manualmente é possivel através do comando:

```shell
docker-compose exec predocs bash scripts/update_db.sh
```

---

- [Documentação do desenvolvedor](/docs/predocs/index.md)
- [index](/docs/index.md)
