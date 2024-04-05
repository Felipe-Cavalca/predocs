#!/bin/bash

# Entra no diretório do projeto
cd /var/www/html/

# Instala o Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala as dependências do projeto
composer install

# Roda o phpUnit
vendor/bin/phpunit ./tests
