FROM php:8.1-fpm

RUN apt-get --allow-releaseinfo-change update && apt-get install -y git \
     mariadb-client libicu-dev curl libcurl4 libcurl3-dev libzzip-dev libzip-dev
RUN docker-php-ext-install curl pdo pdo_mysql intl zip pcntl calendar sysvsem sysvshm sysvmsg exif

WORKDIR /www