# Lipupini

## Requirements

- PHP8
- [Composer](https://getcomposer.org/)
- One of: [PHP GD Extension](https://www.php.net/manual/en/book.image.php), [ImageMagick Extension](https://www.php.net/manual/en/book.imagick.php), [Gmagick Extension](https://www.php.net/manual/en/book.gmagick.php)

## Start Lipupini

```shell
# Install Composer dependencies
cd package
composer install
# Navigate to the webserver document root folder
cd ../plugin/Lipupini/webroot
# Start webserver
php -S localhost:4000 index.php
```

Visit http://localhost:4000/@example

## Note

ActivityPub transactions should occur via HTTPS per spec. https://ngrok.com can be used to get up and running quickly for development.

For example, after starting Lipupini you can run this command:

```shell
ngrok http 4000
```

If your Ngrok URL is then `https://f674-73-83-87-238.ngrok-free.app`, you should be able to query `@example@f674-73-83-87-238.ngrok-free.app` from another Fediverse client.
