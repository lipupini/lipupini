FROM dunglas/frankenphp:1.1.3-php8.2.18-alpine

# composer: PHP dependency library
# ffmpeg: Video processing
RUN apk add --no-cache composer ffmpeg

# PHP Extensions
RUN install-php-extensions gd

# Copy the application files inside the image
COPY ../../../../.. /app/

# Custom PHP config
RUN cp -f /app/system/deploy/docker/frankenphp/php.ini $PHP_INI_DIR/php.ini

# Custom Caddy config
RUN cp -f /app/system/deploy/docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Create a volume for collections
VOLUME /app/collection

# Default webserver root can be deleted
RUN rm -r /app/public

# Install Composer dependencies
RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist
