#!/bin/bash

if ! [ -x "$(command -v git)" ]; then
  echo 'Git não está instalado. Instalando...'
  sudo apt-get install git -y
fi
