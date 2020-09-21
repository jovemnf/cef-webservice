FROM php:7.2-apache

RUN a2enmod rewrite

RUN apt-get update
RUN apt-get install git -y
RUN php /var/www/html/composer.phar update --no-interaction --ansi
RUN apt-get clean
RUN apt-get autoclean

COPY . /var/www/html/
