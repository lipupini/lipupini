# Lipupini

[Requirements](#requirements)

[Start Lipupini](#start-lipupini)

[ActivityPub Note](#activitypub-note)

[The Name](#the-name)

[Demo](#demo)

[Contributing](#contributing)

[Acknowledgements](#acknowledgements)

---

## Requirements

- PHP8
- [Composer](https://getcomposer.org/)
- One of: [ImageMagick Extension](https://www.php.net/manual/en/book.imagick.php), [Gmagick Extension](https://www.php.net/manual/en/book.gmagick.php), [PHP GD Extension](https://www.php.net/manual/en/book.image.php)

## Start Lipupini

```shell
# Install Composer dependencies
cd package
composer install
# Navigate to the webserver document root folder
# The default one is shipped in a plugin called `Lukinview` and symlinked to `webroot` in the project root folder
# You can use another plugin for `webroot` by changing the symlink
cd ../webroot
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

Though ActivityPub implementation is currently limited, the demo is searchable in the Fediverse `@example@lipupini-demo.dup.bz`

The demo runs on Apache2. If you already have Apache2 configured to serve PHP, then you can install Composer dependencies and point the virtual host's `DocumentRoot` to `webroot` and it should "just work."

AI-powered keyword filtering is currently a "hidden feature" due to potential performance issues and ease-of-setup. See `package/rclip/README.md` for more information.

## Contributing

You are welcome to fork it, change it, add plugins! Don't even hesitate to make a PR that includes your own plugin! It could be shipped with or integrated into core.

I hope that the plugin architecture makes for a good workflow, especially being open to merging new plugins. In theory, plugins could just as easily be Composer packages and not have a `plugin` directory at all, but I'd like to keep with this pattern. The composer option can still work seamlessly with this pattern as well.

Email apps@dup.bz if you'd like a point of contact or post in [discussions](https://github.com/instalution/lipupini/discussions) or [issues](https://github.com/instalution/lipupini/issues)! If you begin to find any aspect frustrating or feel that it should be done in a different way, please reach out.

If you want to use Liputini for your artist portfolio or business website, I will support your effort.

## Acknowledgements

Markdown parser: https://parsedown.org

RSA key generation: https://freek.dev/1829-encrypting-and-signing-data-using-privatepublic-keys-in-php

Image processor: https://github.com/php-imagine/Imagine

Folder icon: https://icons8.com/icon/1e4bYxePiOFA/folder

Arrow icons: https://www.svgrepo.com/author/Pictogrammers

AI-powered image keyword search: https://github.com/yurijmikhalevich/rclip

ActivityPub inspiration: [@dansup@pixelfed.social](https://pixelfed.social/dansup)
