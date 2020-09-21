FROM php:7.2-apache

RUN a2enmod rewrite

RUN apt-get update
RUN apt-get install zip libzip-dev unzip git -y
RUN apt-get clean
RUN apt-get autoclean

RUN docker-php-ext-install pdo_mysql mbstring zip

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

RUN php /var/www/html/composer.phar update --no-interaction --ansi
