FROM dunglas/frankenphp:1.1.3-php8.2.18-alpine

ARG APPUSER=lipupini

# composer: PHP dependency library
# ffmpeg: Video processing
RUN apk add --no-cache composer ffmpeg

# PHP Extensions
RUN install-php-extensions gd

# Add and configure the application user
RUN adduser -D ${APPUSER}; \
	# Add additional capability to bind to port 80 and 443
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	# Give write access to /data/caddy and /config/caddy
	chown -R ${APPUSER}:${APPUSER} /data/caddy && chown -R ${APPUSER}:${APPUSER} /config/caddy;

# Copy the application files inside the image
COPY --chown=${APPUSER}:${APPUSER} ../../../../.. /app/

# Custom PHP config
RUN ln -sf /app/system/deploy/docker/frankenphp/php.ini $PHP_INI_DIR/php.ini

# Custom Caddy config
RUN ln -sf /app/system/deploy/docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Create a volume for collections
VOLUME /app/collection

# Switch to the application user
USER ${APPUSER}

# Install Composer dependencies
RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist
