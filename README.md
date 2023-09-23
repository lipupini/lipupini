# Lipupini

## Requirements

- PHP8

## Start Lipupini

```shell
# Navigate to the webserver document root folder
cd view/Lipupini/webroot
# Symlink `collection` folder inside `webroot` folder as "c" if it is not already linked
ln -s ../../../collection c
# Start webserver
php -S localhost:4000 index.php
```

Visit http://localhost:4000/@example

## Vision

While not required, Lipupini can run in production using PHP's built-in webserver, and an HTTPS proxy will be created using Nginx, Caddy, Apache, or Ngrok.

When using `systemd`, the service might look like this in `/etc/systemd/system/lipupini.service`:

```ini
[Unit]
Description=Lipupini
After=network.target
Requires=nginx

[Service]
Type=simple
ExecStart=/usr/bin/php -S localhost:80 index.php -t /opt/webapp/lipupini/view/Lipupini/webroot/
User=www-data
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

- Replace `/opt/webapp/lipupini/` with the project root path
- Replace `User=www-data` with the user account under which the Lipupini process should be run
- Replace `Requires=nginx` accordingly for a different HTTPS proxy
- Proxy port 80 to HTTPS however you will

This pattern offers the following benefits:

- There are far fewer init systems than Linux distributions, so the Liputini service can be the same for any distribution that uses a particular system.
- PHP's built-in webserver is performant, and you can put the whole setup behind Cloudflare if you feel that you need boatloads of visitors every second of every day.
- The method to create an HTTPS proxy for each webserver is more boilerplate than configuring each webserver for PHP and application specifics.
- Allowing the use of Ngrok for HTTPS could drastically speed up deployment time without requiring a separate webserver at all.
