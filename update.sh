#!/bin/bash

# Parar os serviços
docker-compose down

# Atualizar o código do projeto
git pull

# Reconstruir e reiniciar os serviços
docker-compose up --build -d

# Instala dependencias
docker-compose exec predocs bash scripts/install_dependencies.sh

# Atualiza o banco de dados
docker-compose exec predocs bash scripts/update_bd.sh
