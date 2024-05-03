#!/bin/bash

# Instala dependencias
docker-compose exec predocs bash scripts/install_dependencies.sh

# Instala o banco de dados
docker-compose exec predocs bash scripts/install_db.sh

# Atualiza o banco de dados
docker-compose exec predocs bash scripts/update_db.sh
