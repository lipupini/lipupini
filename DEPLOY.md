This document outlines deployment processes and other DevOps concerns.

[Deploying with Docker](#deploying-with-docker)

[System Dependencies](#system-dependencies)

[Installing System Dependencies](#installing-system-dependencies)

[Using PHP's Built-in Webserver](README.md#lipupini-development-server)

---

## Deploying with Docker

Build the Docker image from the project directory:

```shell
# Start in the project root
cd path/to/project/root
# Build the image
docker build --tag lipupini .
```

If you have [Just](https://github.com/casey/just/) installed:

```shell
# Start in the project root
cd path/to/project/root
# Build the image
just docker-build
```

There are multiple ways to run the image once built:

```shell
# Start in the project root
cd path/to/project/root
# Run the image using the `docker` command
docker run lipupini
# Or using a `just` command
just docker-run
```

Lipupini should then be available at `localhost:4000`

## System Dependencies

When not using Docker to run Lipupini, you'll need to make sure system dependencies are met.

Note: Some distros may already include varying extensions bundled with PHP.

- [PHP8](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/)
- One of: [ImageMagick Extension](https://www.php.net/manual/en/book.imagick.php), [Gmagick Extension](https://www.php.net/manual/en/book.gmagick.php), or [PHP GD Extension](https://www.php.net/manual/en/book.image.php)
- [PHP cURL Extension](https://www.php.net/manual/en/book.curl.php)
- [PHP DOM Extension](https://www.php.net/manual/en/book.dom.php)

## Installing System Dependencies

Debian 12

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

Ubuntu 23.10

```shell
sudo apt update -y
sudo apt install -y git php php-gd php-curl php-dom composer
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
sudo pacman -Sy
sudo pacman -Sy git php php-gd composer
# Edit `php.ini`, e.g.:
sudo vi /etc/php/php.ini
# Uncomment the following extension lines to enable them:
# extension=gd
```

Alpine Linux (tested with 3.18)

```shell
# Make sure that both "main" and "community" repositories are enabled
sudo apk update
sudo apk add git php php-gd php-curl php-dom composer
```

Slackware 15

```shell
sudo /usr/sbin/slackpkg install php81
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --filename=composer --install-dir=/usr/bin
php -r "unlink('composer-setup.php');"
```

Here is a screenshot of Lipupini running locally on Android :D Post in [discussions](https://github.com/lipupini/lipupini/issues) if you would like to see this process documented with instructions.

![IMG_20231019_124846](https://github.com/lipupini/lipupini/assets/108841276/60785bb6-9caf-424a-8abe-735684657deb)
