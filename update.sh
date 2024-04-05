#!/bin/bash

# Parar os serviços
docker-compose down

# Atualizar o código do projeto
git pull

# Reconstruir e reiniciar os serviços
docker-compose up --build -d

# Instala o banco de dados
scripts/update_db.sh
