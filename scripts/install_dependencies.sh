#!/bin/bash

# Instala o Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Entra no diretório do projeto
cd /var/www/html/

# Instala as dependências do projeto
composer install
