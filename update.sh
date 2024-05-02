#!/bin/bash

# Instalar o Docker e o Docker Compose se eles não estiverem instalados
if ! [ -x "$(command -v docker)" ]; then
  echo 'Docker não está instalado. Instalando...'
  sudo apt-get install docker.io -y
fi

if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Docker Compose não está instalado. Instalando...'
  sudo apt-get install docker-compose -y
fi

# Parar os serviços
docker-compose down

# Atualizar o código do projeto
git pull

# Reconstruir e reiniciar os serviços
docker-compose up --build -d

# Instala dependencias
docker-compose exec predocs bash scripts/install_dependencies.sh

# Atualiza o banco de dados
docker-compose exec predocs bash scripts/update_db.sh
