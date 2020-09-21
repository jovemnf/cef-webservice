FROM php:7.2-apache

RUN a2enmod rewrite

RUN apt-get update
RUN apt-get install zip git -y
RUN apt-get clean
RUN apt-get autoclean

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

COPY . /var/www/html/

RUN php /var/www/html/composer.phar update --no-interaction --ansi
