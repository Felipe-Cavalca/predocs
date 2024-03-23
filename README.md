# Predocs - Um Framework Web

## Visão Geral

O Predocs é um framework para desenvolvimento web voltado para programadores que desejam criar aplicativos web com PHP, seguindo principios de API FIRST. Ele simplifica a conexão com bancos de dados e oferece uma arquitetura para agilizar o desenvolvimento.

## Requisitos de Sistema

1. git
1. docker
1. docker-compose

## Configuração para instalação

### Docker
#### Banco de dados
para alterar as configurações do banco de dados mude o arquivo `Docker/Dockerfile.bd`

### Front
Para que o front consiga reconhecer as urls dos outros containers é preciso configurar o arquivo `app/config/settings.json`

### Server
As configurações de ambientes do servidor ficam localizados nos arquivos `/server/settings.env` e nos arquivos da pasta `/server/Envs`

## Instalação

1. Clone o repositorio com o comando: ```git clone https://github.com/Felipe-Cavalca/predocs```

1. Navegue a pasta onde o projeto foi clonado
1. Execute o comando: ```docker-compose up```
1. Aguarde a instalação do projeto.

Após a instalação o sistema estara pronto para uso.

## Links

### Front
O frontend do sistema roda no endereço localhost:8000

### Back
O backend do sistema roda no endereço localhost:9000

### Banco
A base de dados roda na porta 3306

## Contribuição


## Política de Segurança

Para informações sobre como relatar vulnerabilidades, consulte nossa [Política de Segurança](SECURITY.md).

## Contato

Se você tiver dúvidas, sugestões ou precisar de suporte, entre em contato conosco pelo Discord ou na guia "Insulle" deste repositório.

## Licença

Este projeto é licenciado sob [LICENSE](LICENSE).
