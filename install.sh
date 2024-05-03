#!/bin/bash

# Url do repositório do GitHub
repo_url="https://github.com/Felipe-Cavalca/predocs.git"

# Extrair o nome do repositório da URL do Git
repo_name=$(basename "$repo_url" .git)

# Executa os scripts
source ./scripts/update/verify_root.sh
source ./scripts/update/update_sistem.sh
source ./scripts/update/verify_docker.sh
source ./scripts/update/verify_git.sh

# Clonar o repositório do GitHub
git clone $repo_url

# Navegar para o diretório do projeto
cd $repo_name

# Construir e iniciar os serviços usando o Docker Compose
docker-compose up -d

# Executa os scripts do projeto
source ./scripts/update/execute_scripts.sh
