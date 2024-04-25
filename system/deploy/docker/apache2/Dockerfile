FROM php:8.2.18-apache-bullseye

# WIP: Pleaes contribute

ARG APPUSER=www-data

# ffmpeg: Video processing
RUN apt-get update && apt-get -y install ffmpeg

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Development libraries required by PHP extensions
RUN apt-get -y install libavif-dev libcurl4-openssl-dev libjpeg-dev libpng-dev zlib1g-dev

# PHP Extensions
RUN docker-php-ext-configure gd --with-jpeg --with-avif
RUN docker-php-ext-install gd curl

# Copy the application files inside the image

COPY --chown=${APPUSER}:${APPUSER} ../../../../.. /app/
RUN rm -r /var/www/html
RUN ln -sf /app/module/Lukinview/webroot /var/www/html

# Create a volume for collections
VOLUME /app/collection

# Install Composer dependencies
RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist
