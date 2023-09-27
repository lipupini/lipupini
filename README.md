# Lipupini

## Requirements

- PHP8

## Start Lipupini

```shell
# Navigate to the webserver document root folder
cd view/Lipupini/webroot
# Start webserver
php -S localhost:4000 index.php
```

Visit https://f673-72-83-87-238.ngrok-free.app:4000/@example

## Note

ActivityPub transactions should occur via HTTPS per spec. https://ngrok.com can be used to get up and running quickly for development.

For example, after starting Lipupini you can run this command:

```shell
ngrok http 4000
```

If your Ngrok URL is then for example `https://f674-73-83-87-238.ngrok-free.app`, then you should be able to query `@example@f674-73-83-87-238.ngrok-free.app` from another Fediverse client.
