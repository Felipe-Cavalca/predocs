#!/bin/bash

# Url do repositório do GitHub
repo_url="https://github.com/Felipe-Cavalca/predocs.git"

# Extrair o nome do repositório da URL do Git
repo_name=$(basename "$repo_url" .git)

# Verificar se o script está sendo executado como root
if (( $EUID != 0 )); then
  echo "Por favor, execute como root"
  exit
fi

# Atualiza o sistema
sudo apt update && sudo apt upgrade -y

# Instalar o Docker e o Docker Compose se eles não estiverem instalados
if ! [ -x "$(command -v docker)" ]; then
  echo 'Docker não está instalado. Instalando...'
  sudo apt-get install docker.io -y
fi

if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Docker Compose não está instalado. Instalando...'
  sudo apt-get install docker-compose -y
fi

# Instalar o Git se ele não estiver instalado
if ! [ -x "$(command -v git)" ]; then
  echo 'Git não está instalado. Instalando...'
  sudo apt-get install git -y
fi

# Clonar o repositório do GitHub
git clone $repo_url

# Navegar para o diretório do projeto
cd $repo_name

# Construir e iniciar os serviços usando o Docker Compose
docker-compose up -d
