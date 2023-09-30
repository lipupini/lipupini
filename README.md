# Lipupini

[Status](#status)

[System Dependencies](#system-dependencies)

[Lipupini Quickstart](#lipupini-quickstart)

[Add Your Collection](#add-your-collection)

[ActivityPub Note](#activitypub-note)

[The Name](#the-name)

[Demo](#demo)

[Contributing](#contributing)

[Acknowledgements](#acknowledgements)

---

![image](https://github.com/instalution/lipupini/assets/108841276/843f8a31-0d6c-42d2-a366-c355b03986a6)

---

## Status

For displaying a media portfolio/posts on the Internet, despite currently limited ActivityPub support Lipupini is considered to be **production-ready**.

1) Install dependencies and clone the repository.
2) Add and [initialize](collection/README.md) your collection, customize `.lipupini/.files.json` with captions, and delete the example collection.
3) Ensure that your files display. If they don't, convert them to supported formats.
4) Deploy to a PHP server.

Updating Lipupini should be as simple as running `git pull` from your environment.

## System Dependencies

- [PHP8](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/)
- One of: [ImageMagick Extension](https://www.php.net/manual/en/book.imagick.php), [Gmagick Extension](https://www.php.net/manual/en/book.gmagick.php), [PHP GD Extension](https://www.php.net/manual/en/book.image.php)

## Lipupini Quickstart

1) Clone the app and `cd` into the project root

```shell
git clone git@github.com:instalution/lipupini.git
cd lipupini
```

2) Install Composer dependencies and go back to project root

```shell
cd package
composer install
cd ..
```

3. Navigate to the webserver document root folder symlinked from the `Lukinview` plugin and start PHP's built-in webserver. See [plugin/Lukinview/README.md](plugin/Lukinview/README.md) for more information

```shell
cd webroot
php -S localhost:4000 index.php
```

4. Visit http://localhost:4000/@example

## Add Your Collection

Say you have a folder of awesome photos at `/home/sally/Pictures/AwesomePhotos`

Your Lipupini installation is at `/opt/webapp/lipupini`

1) Take the photos from `/home/sally/Pictures/AwesomeCollection` and put them into the collection directory `/opt/webapp/lipupini/collection/sally` either by copying them:

```shell
cp -R /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/collection/sally
```

or symlinking them, in which case any compatible photos added in `/home/sally/Pictures/AwesomeCollection` will automatically be served by Lipupini:

```shell
ln -s /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/collection/sally
```

2) Initialize the `.lipupini` folder for the collection

```shell
cd /opt/webapp/lipupini
bin/create-basic-lipupini-folder-in-collection.php sally
```

3) Save a file called `.avatar.png` at `/opt/webapp/lipupini/collection/sally/.lipupini/.avatar.png`

4) Edit the file at `/opt/webapp/lipupini/collection/sally/.lipupini/.files.json` to add captions (this is optional)

5) Delete the example collection:

```shell
rm -r collection/example
```

6) Sally's collection should now be viewable at http://localhost:4000/@sally

In addition to copying or symlinking, see [collection/README.md#vision](collection/README.md#vision) for ideas on other ways to keep these directories in sync.

## ActivityPub Note

ActivityPub transactions should occur via HTTPS per spec. https://ngrok.com can be used to get up and running quickly for development.

For example, after starting Lipupini you can run this command:

```shell
ngrok http 4000
```

If your Ngrok URL is then `https://f674-73-83-87-238.ngrok-free.app`, you should be able to query `@example@f674-73-83-87-238.ngrok-free.app` from another Fediverse client.

Using Ngrok, with an upgraded plan you can setup a fairly restrictive port firewall, configure it to run on startup, and reliably host any domain with HTTPS.

## The Name

"Lipupini" is a "word formed by combining other words" (portmanteau), and "lipu pini" in this context translates to "past document" in [Toki Pona core](https://zrajm.github.io/toki-pona-syllabics/dictionary/). Lipupini is for organizing computer files like images, videos, sounds and writings that you might want to display with Fediverse support on the Internet under your domain.

## Demo

Here is what it can look like so far: https://lipupini-demo.dup.bz/@example

Though ActivityPub implementation is currently limited, the demo is searchable in the Fediverse `@example@lipupini-demo.dup.bz`

The demo runs on Apache2. If you already have Apache2 configured to serve PHP, then you can install Composer dependencies and point the virtual host's `DocumentRoot` to `webroot` and it should "just work."

AI-powered keyword filtering is currently a "hidden feature" due to potential performance issues and ease-of-setup. See [package/rclip/README.md](package/rclip/README.md) for more information.

## Contributing

You are welcome to fork it, change it, add plugins! Please don't hesitate to make a PR that includes your own plugins - it could be shipped with or integrated into core.

I hope that the plugin architecture makes for a good workflow, especially being open to merging new plugins. In theory, plugins could just as easily be Composer packages and not have a `plugin` directory at all, but I'd like to keep with this pattern. The composer option can still work seamlessly with this pattern as well.

Email apps@dup.bz if you'd like a point of contact or post in [discussions](https://github.com/instalution/lipupini/discussions) or [issues](https://github.com/instalution/lipupini/issues)! If you begin to find any aspect frustrating or feel that it should be done in a different way, please reach out.

If you want to use Lipupini for your artist portfolio or business website, I will support your effort.

## Acknowledgements

VanJS: https://vanjs.org

Markdown parser: https://parsedown.org

RSA key generation: https://freek.dev/1829-encrypting-and-signing-data-using-privatepublic-keys-in-php

Image processor: https://github.com/php-imagine/Imagine

Folder icon: https://icons8.com/icon/1e4bYxePiOFA/folder

Arrow icons: https://www.svgrepo.com/author/Pictogrammers

AI-powered image keyword search: https://github.com/yurijmikhalevich/rclip

ActivityPub inspiration: [@dansup@pixelfed.social](https://pixelfed.social/dansup)
