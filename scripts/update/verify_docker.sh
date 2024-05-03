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
