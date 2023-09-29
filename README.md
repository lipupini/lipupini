# Lipupini

[Requirements](#requirements)

[Start Lipupini](#start-lipupini)

[ActivityPub HTTPS Note](#activitypub-https-note)

[The Name](#the-name)

[Demo](#demo)

---

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

## ActivityPub Note

ActivityPub transactions should occur via HTTPS per spec. https://ngrok.com can be used to get up and running quickly for development.

For example, after starting Lipupini you can run this command:

```shell
ngrok http 4000
```

If your Ngrok URL is then `https://f674-73-83-87-238.ngrok-free.app`, you should be able to query `@example@f674-73-83-87-238.ngrok-free.app` from another Fediverse client.

## The Name

"Lipupini" is a "word formed by combining other words" (portmanteau), and "lipu pini" in this context translates to "past document" in [Toki Pona core](https://zrajm.github.io/toki-pona-syllabics/dictionary/). Lipupini is for organizing computer files like images, videos, sounds and writings that you might want to display on the Internet under a domain that you own.

## Demo

Here is what it can look like so far: https://lipupini-demo.dup.bz/@example

Though ActivityPub implementation is currently limited, this demo is searchable in the Fediverse `@example@lipupini-demo.dup.bz`

The demo runs on Apache2. If you already have Apache2 configured to serve PHP, then you can install Composer dependencies and point the virtual host's `DocumentRoot` to `plugin/Lipupini/webroot` and it should "just work."
