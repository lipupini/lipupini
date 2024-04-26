FROM --platform=linux/amd64 dunglas/frankenphp:static-builder

# composer: PHP dependency library
# ffmpeg: Video processing
RUN apk add --no-cache composer ffmpeg

# Copy your app
WORKDIR /go/src/app/dist/app
COPY ../../../../.. .

# Custom PHP config
RUN cp -f /app/system/deploy/docker/frankenphp/php.ini $PHP_INI_DIR/php.ini

# Custom Caddy config
RUN cp -f /app/system/deploy/docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Build the static binary, be sure to select only the PHP extensions you want
WORKDIR /go/src/app/
RUN EMBED=dist/app/ \
    PHP_EXTENSIONS=gd \
    ./build-static.sh
