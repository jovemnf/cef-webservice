FROM php:7.2-apache

RUN a2enmod rewrite

//COPY . /var/www/html/

RUN apt-get update
RUN apt-get install git -y
#RUN php /var/www/html/composer.phar update --no-interaction --ansi
RUN apt-get clean
RUN apt-get autoclean
