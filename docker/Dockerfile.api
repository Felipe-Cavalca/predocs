# Dockerfile.api
FROM php:8.3-apache

# instalando os módulos necessários
RUN a2enmod rewrite
RUN a2enmod headers
RUN docker-php-ext-install pdo_mysql

# instalando a extensão PHP Redis
RUN pecl install redis && docker-php-ext-enable redis

RUN service apache2 restart
