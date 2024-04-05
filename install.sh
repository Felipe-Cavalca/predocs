#!/bin/bash

# Url do repositório do GitHub
repo_url="https://github.com/Felipe-Cavalca/predocs.git"

# Extrair o nome do repositório da URL do Git
repo_name=$(basename "$repo_url" .git)

# Atualizar os pacotes do sistema
sudo apt update

# Fazer o upgrade dos pacotes do sistema
sudo apt upgrade -y

# Instalar o Git
sudo apt install -y git

# Clonar o repositório do GitHub
git clone $repo_url

# Navegar para o diretório do projeto
cd $repo_name

# Instalar o Docker e o Docker Compose se eles não estiverem instalados
if ! [ -x "$(command -v docker)" ]; then
  echo 'Docker não está instalado. Instalando...'
  sudo apt-get install docker.io -y
fi

if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Docker Compose não está instalado. Instalando...'
  sudo apt-get install docker-compose -y
fi

# Construir e iniciar os serviços usando o Docker Compose
docker-compose up --build -d

# Instala dependencias
docker-compose exec predocs bash scripts/install_dependencies.sh

# Instala o banco de dados
docker-compose exec predocs bash scripts/install_bd.sh
