FROM php:8.3.4-alpine3.19

RUN apk update && apk add --no-cache wget \
	&& wget -qO /usr/bin/composer https://github.com/composer/composer/releases/download/2.7.2/composer.phar \
	&& chmod +x /usr/bin/composer \
	# We only needed `wget` for installing `composer`
	&& apk del wget

COPY . /app/
VOLUME /app/collection

RUN cd /app/module/Lipupini && composer install --no-interaction --no-dev --prefer-dist

EXPOSE 4000
CMD ["ash", "-c", "PHP_CLI_SERVER_WORKERS=2 php -S 127.0.0.1:4000 -t /app/module/Lukinview/webroot"]
