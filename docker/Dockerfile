FROM unit:php8.2 AS base

EXPOSE 4000

ARG PHP_COMPOSER_VERSION=2.6.5
ARG DEBIAN_NONINTERACTIVE=1

RUN apt-get update && apt-get install -y libjpeg-dev libpng-dev zlib1g-dev wget zip \
	&& docker-php-ext-configure gd --enable-gd --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& wget -qO /usr/bin/composer https://github.com/composer/composer/releases/download/${PHP_COMPOSER_VERSION}/composer.phar \
	&& chmod +x /usr/bin/composer \
	&& apt-get remove -y wget \
	&& apt-get clean \
	&& rm --recursive --force /var/lib/apt/lists/* /tmp/*

ARG APP_UID=1000
ARG APP_GID=1000
RUN getent group "$APP_GID" || ( \
		groupadd --gid ${APP_GID} app \
	) \
	&& getent passwd "$APP_UID" || ( \
		useradd --create-home --gid $(getent group ${APP_GID} | cut --delimiter=: --fields=1) --uid ${APP_UID} app \
	)

# ---

FROM base AS prod
COPY ./module/Lipupini/composer.* /app/module/Lipupini/
RUN cd /app/module/Lipupini \
	&& composer install --prefer-dist --no-progress --optimize-autoloader --no-dev
COPY --chown=root:app ./ /app/
COPY ./docker/etc/unit/* /docker-entrypoint.d/
RUN chmod -R g+w /app/storage /app/module/Lukinview/webroot/c
VOLUME /app/collection

# ---

FROM base AS dev
VOLUME /app
COPY ./docker/etc/unit/* /docker-entrypoint.d/
WORKDIR /app
