FROM php:8.2.17-alpine3.19

# composer: PHP dependency library
# ffmpeg: Video processing (optional)
# tini: Tiny Docker init library
RUN apk add --no-cache composer ffmpeg tini
# Development libraries required by PHP extensions
RUN apk add --no-cache zlib-dev libpng-dev libjpeg-turbo-dev libavif-dev curl-dev
# Now we can configure and install the extensions
RUN docker-php-ext-configure gd --with-jpeg --with-avif
RUN docker-php-ext-install gd curl

COPY ../.. /app/
VOLUME /app/collection

RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist

ENTRYPOINT ["tini", "--"]
CMD ["ash", "-c", "cd /app/module/Lukinview/webroot && PHP_CLI_SERVER_WORKERS=2 php -S 0.0.0.0:4000 index.php"]

WORKDIR /app
EXPOSE 4000
