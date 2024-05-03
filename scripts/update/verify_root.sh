#!/bin/bash

# Verificar se o script está sendo executado como root
if (( $EUID != 0 )); then
  echo "Por favor, execute como root"
  exit
fi
