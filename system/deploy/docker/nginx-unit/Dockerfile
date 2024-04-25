FROM unit:php8.2

EXPOSE 8080

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

RUN apt-get clean && rm --recursive --force /var/lib/apt/lists/* /tmp/*

# Copy Nginx Unit config
COPY ../../../../../system/deploy/docker/nginx-unit/etc/unit/* /docker-entrypoint.d/

# Copy the application files inside the image
COPY ../../../../.. /app/

VOLUME /app/collection

# Install Composer dependencies
RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist
