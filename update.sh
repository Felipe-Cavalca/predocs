#!/bin/bash

# Executa os scripts
source ./scripts/update/verify_root.sh
source ./scripts/update/update_sistem.sh
source ./scripts/update/verify_docker.sh
source ./scripts/update/verify_git.sh

# Parar os serviços
docker-compose down

# Atualizar o código do projeto
git pull

# Reconstruir e reiniciar os serviços
docker-compose up --build -d

# Executa os scripts do projeto
source ./scripts/update/execute_scripts.sh
