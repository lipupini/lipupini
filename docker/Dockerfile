FROM unit:php8.2 AS base

EXPOSE 4000

ARG PHP_COMPOSER_VERSION=2.6.5
ARG DEBIAN_NONINTERACTIVE=1

RUN apt-get update && apt-get install -y libpng-dev zlib1g-dev wget zip \
  && docker-php-ext-install gd \
  && wget -qO /usr/bin/composer https://github.com/composer/composer/releases/download/${PHP_COMPOSER_VERSION}/composer.phar \
  && chmod +x /usr/bin/composer \
  && apt-get remove -y wget \
  && apt-get clean \
  && rm --recursive --force /var/lib/apt/lists/* /tmp/*

# ---

FROM base AS prod
COPY ./module/Lipupini/composer.* /app/module/Lipupini/
RUN cd /app/module/Lipupini \
  && composer install --prefer-dist --no-progress --optimize-autoloader --no-dev
COPY ./ /app/
COPY ./docker/etc/unit/* /docker-entrypoint.d/
RUN chown -R root:unit /app \
  && chmod -R g+w /app/collection \
  && chmod -R g+w /app/module/Lukinview/webroot

# ---

FROM base AS dev
ARG APP_UID=1000
ARG APP_GID=1000
VOLUME /app
RUN apt-get update && apt-get install --yes curl git vim zip \
  && getent group "$APP_GID" || ( \
    groupadd --gid ${APP_GID} app \
  ) \
  && getent passwd "$APP_UID" || ( \
    useradd --create-home --gid $(getent group ${APP_GID} | cut --delimiter=: --fields=1) --uid ${APP_UID} app \
  ) \
  && apt-get clean && rm --recursive --force /var/lib/apt/lists/*
COPY ./docker/etc/unit/* /docker-entrypoint.d/
WORKDIR /app
