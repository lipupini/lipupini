# Lipupini

[Status](#status)

[Features](#features)

[Lipupini Docker Setup](DEPLOY.md#deploying-with-docker)

[Running Lipupini Locally for Development](#lipupini-development-server)

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
- Module system paves a way for collaborative development.
- Minimalist grid layout. Frontend is ready to be customized, or you can make an entirely new frontend module.
- Building a way to keyword search collections using AI image recognition.
- On-demand caching system creates and serves static media files. Support for custom caching URL can facilitate the use of a CDN.
- A [Public Domain license](LICENSE.md) is the most permissive license there is. You can do whatever you want with this thing. Please feel free to contribute back to upstream, post in discussions, etc. There is no obligation of any kind.

## Lipupini Development Server

Make sure all [dependencies are installed first](DEPLOY.md#installing-system-dependencies).

1) Clone the app and `cd` into the project root

```shell
git clone https://github.com/instalution/lipupini.git
cd lipupini
```

2) Install Composer dependencies and go back to project root

```shell
cd module/Lipupini
composer install
cd ../..
```

3. Navigate to the webserver document root and start PHP's built-in webserver. See [module/Lukinview/README.md](module/Lukinview/README.md) for more information.

```shell
cd module/Lukinview/webroot
PHP_CLI_SERVER_WORKERS=2 php -S localhost:4000 index.php
```

4. Visit http://localhost:4000/@example

## Add Your Collection

Say you have a folder of awesome photos at `/home/sally/Pictures/AwesomePhotos`

Your Lipupini installation is at `/opt/webapp/lipupini`

1) Take the photos from `/home/sally/Pictures/AwesomeCollection` and put them into the collection directory `/opt/webapp/lipupini/storage/collection/sally` either by copying them:

```shell
cp -R /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/storage/collection/sally
```

or symlinking them:

```shell
ln -s /home/sally/Pictures/AwesomeCollection /opt/webapp/lipupini/storage/collection/sally
```

2) Initialize the `.lipupini` folder for the collection

```shell
cd /opt/webapp/lipupini
bin/generate-keys.php sally
bin/create-files-json.php sally
```

3) Save a file called `.avatar.png` at `/opt/webapp/lipupini/storage/collection/sally/.lipupini/.avatar.png`

4) Edit the file at `/opt/webapp/lipupini/storage/collection/sally/.lipupini/.files.json` to add captions (this is optional)

5) Delete the example collection:

```shell
rm -r collection/example
```

6) Your collection should now be viewable at http://localhost:4000/@sally

In addition to copying or symlinking, see [storage/collection/README.md#vision](storage/collection/README.md#vision) for ideas on other ways to keep these directories in sync.

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

You are welcome to fork it, change it, add modules! Please don't hesitate to make a PR that includes your own modules - it could be shipped with or integrated into core.

I hope that the module architecture makes for a good workflow, especially being open to merging new modules. In theory, modules could just as easily be Composer packages and not have a `module` directory at all. The current architecture can still work seamlessly with the Composer pattern as well.

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
- New window from frontend, might not need to use the `Parsedown.php` extension
- Make `bin/generate-files-json.php` recursive
- Create script to normalize file and directory user/group/permissions
- Look into:
  - https://indieweb.org/Webmention
  - https://indieweb.org/Microsub
  - https://indieweb.org/Micropub
  - https://micropub.rocks
- In `bin/generate-files-json.php`, read EXIF data if available for setting a default `date`
- Contributions to `landrok/activitypub`:
  - Propose error messages in `HttpSignature.php` be sent back to requester
  - Propose adding an `.editorconfig`
