# Lipupini

[Status](#status)

[Features](#features)

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

For displaying a media portfolio or posts on the Internet, despite currently limited ActivityPub support Lipupini is considered to be **production-ready**.

1) Install dependencies and clone the repository.
2) Add and [initialize](#add-your-collection) your collection, customize `.lipupini/.files.json` with captions, and delete the example collection.
3) Ensure that your files display. If they don't, convert them to supported formats.
4) Deploy to a PHP server.

Updating Lipupini can be as simple as running `git pull` from your environment depending on the setup.

## Features

- Supports the following formats for media files, more will be added: JPG, PNG, MP4, MP3, Markdown
- Allows subscribing to your content collection via RSS2.0.
- Search Lipupini accounts from other Fediverse platforms via the ActivityPub protocol.
- Show an avatar PNG when searching from an external ActivityPub or RSS client.
- Once dependencies are installed, Lipupini is designed to get up and running quickly.
- Your media collections are self-contained, served as they are on your filesystem. Metadata is stored in a special `.lipupini` folder, making account collections completely portable.
- Lipupini manages to implement ActivityPub without a database. For example, certain inbox activities are logged to your collection in raw JSON.
- Plugin system paves a way for modular development.
- Minimalist grid layout. Frontend is ready to be customized, or you can make an entirely new frontend plugin.
- Building a way to keyword search collections using AI image recognition.
- On-demand caching system creates and serves static media files. Support for custom caching URL can facilitate the use of a CDN.
- A Public Domain license is the most permissive license there is. You can do whatever you want with this thing. Please feel free to contribute back to upstream, post in discussions, etc. There's no obligation of any kind.

## System Dependencies

Some distros may include varying PHP extensions with PHP.

- [PHP8](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/)
- One of: [ImageMagick Extension](https://www.php.net/manual/en/book.imagick.php), [Gmagick Extension](https://www.php.net/manual/en/book.gmagick.php), or [PHP GD Extension](https://www.php.net/manual/en/book.image.php)
- [PHP cUrl Extension](https://www.php.net/manual/en/book.curl.php)
- [PHP DOM Extension](https://www.php.net/manual/en/book.dom.php)

Ubuntu 23.10

```shell
sudo apt update -y
sudo apt install -y git php php-gd php-curl php-dom composer
```

Fedora 38

```shell
sudo dnf update -y
sudo dnf install -y php php-gd php-curl php-dom composer
```

CentOS Stream 9

```shell
sudo yum update -y
sudo yum install -y git php php-gd php-curl php-dom
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --filename=composer --install-dir=/usr/local/bin
php -r "unlink('composer-setup.php');"
```

Void Linux

```shell
sudo xbps-install -Su
sudo xbps-install git php php-gd composer
# Edit `php.ini`, e.g.:
sudo vi /etc/php8.2/php.ini
# Uncomment the following extension lines to enable them:
# extension=curl
# extension=gd
```

Arch Linux

```shell
pacman -Sy
pacman -Sy git php php-gd composer
# Edit `php.ini`, e.g.:
sudo vi /etc/php/php.ini
# Uncomment the following extension lines to enable them:
# extension=gd
```

Alpine Linux (tested with 3.18)

```shell
# Make sure that both "main" and "community" repositories are enabled
apk update
apk add git php php-gd php-curl php-dom composer
```

Slackware 15

```shell
sudo /usr/sbin/slackpkg install php81
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --filename=composer --install-dir=/usr/bin
php -r "unlink('composer-setup.php');"
```

## Lipupini Quickstart

1) Clone the app and `cd` into the project root

```shell
git clone https://github.com/instalution/lipupini.git
cd lipupini
```

2) Install Composer dependencies and go back to project root

```shell
cd package
composer install
cd ..
```

3. Navigate to the webserver document root and start PHP's built-in webserver. See [plugin/Lukinview/README.md](plugin/Lukinview/README.md) for more information.

```shell
cd plugin/Lukinview/webroot
PHP_CLI_SERVER_WORKERS=2 php -S localhost:4000 index.php
```

4. Visit http://localhost:4000/@example

## Add Your Collection

Say you have a folder of awesome photos at `/home/sally/Pictures/AwesomePhotos`

Your Lipupini installation is at `/opt/webapp/lipupini`

1) Take the photos from `/home/sally/Pictures/AwesomeCollection` and put them into the collection directory `/opt/webapp/lipupini/collection/sally` either by copying them:

```shell
cp -R /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/collection/sally
```

or symlinking them, in which case any compatible photos added in `/home/sally/Pictures/AwesomeCollection` will be automatically served by Lipupini:

```shell
ln -s /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/collection/sally
```

2) Initialize the `.lipupini` folder for the collection

```shell
cd /opt/webapp/lipupini
bin/generate-keys.php sally
bin/create-files-json.php sally
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

"Lipupini" is a "word formed by combining other words" (portmanteau), and "lipu pini" in this context translates to "past document" in [Toki Pona core](https://zrajm.github.io/toki-pona-syllabics/dictionary/). Lipupini is for organizing computer files like images, videos, sounds and writings that you might want to display under your domain on the Internet.

## Demo

Here is what it can look like so far: https://lipupini-demo.dup.bz/@example

Though ActivityPub implementation is currently limited, the demo is searchable in the Fediverse `@example@lipupini-demo.dup.bz`

**NOTE:** Please use [activitypub.academy](https://activitypub.academy) Mastondon server for testing, as this is a test server. Some production servers like Mastodon.social won't load it in search. [/kbin](https://kbin.pub) instances should work correctly.

The demo is the `demo` branch running on Apache2. If you already have Apache2 configured to serve PHP, you can install Composer dependencies and point the virtual host's `DocumentRoot` to `webroot` and it should "just work."

## Contributing

You are welcome to fork it, change it, add plugins! Please don't hesitate to make a PR that includes your own plugins - it could be shipped with or integrated into core.

I hope that the plugin architecture makes for a good workflow, especially being open to merging new plugins. In theory, plugins could just as easily be Composer packages and not have a `plugin` directory at all. The current architecture can still work seamlessly with the Composer pattern as well.

Email apps [at] dup.bz if you'd like a point of contact or post in [discussions](https://github.com/instalution/lipupini/discussions) or [issues](https://github.com/instalution/lipupini/issues)! Please reach out if you begin to find any aspect frustrating or feel that it should be done in a different way.

If you want to use Lipupini for your artist portfolio or business website, I will support your effort.

## Acknowledgements

VanJS: https://vanjs.org

Markdown parser: https://parsedown.org

Image processor: https://github.com/php-imagine/Imagine

Folder icon: https://icons8.com/icon/1e4bYxePiOFA/folder

Arrow icons: https://www.svgrepo.com/author/Pictogrammers

AI-powered image keyword search: https://github.com/yurijmikhalevich/rclip

ActivityPub inspiration: [@dansup@pixelfed.social](https://pixelfed.social/dansup)

PHP Secure Communications Library: https://phpseclib.com

Landrok's ActivityPub library: https://github.com/landrok/activitypub

## TODO

- Validate HTTP signatures, this could help improve outgoing signature flow too
- i18n
- Removing a folder "Type" (without file extension) from `.files.json` results in unexpected behavior in the rendering
- All plugin output gets added to a buffer. This way any headers can be modified before output.
    - Currently in the `shutdown()` method of `Lipupini.php` the timing and `X-Powered-By` header is commented out, but it should be possible to send those before output.
- New window from frontend, might not need to use the `Parsedown.php` extension
- Optionally specify separate URL in config for cache files (`c` folder)
- Look into X-Frame-Options "SAMEORIGIN" header
- Make `bin/generate-files-json.php` recursive
- Check out Laravel middleware for additional security ideas
