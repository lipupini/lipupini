The Selopini module namespace folder contains DevOps-related resources

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
