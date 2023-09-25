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

Visit http://localhost:4000/@example

## Note

ActivityPub transactions should occur via HTTPS per spec. The default collection name `example@localhost` will need to be updated for remote communication.

When requesting account display or JSON, for example `http://localhost:4000/@example` via the handle `@example@locahost`, for now the directory in the `collections` folder must match exactly the identifier. So if you make a proxy, e.g. `testlipupini.proxy.ngrok.com`, the identifier folder will need to be renamed or copied to `exmaple@testlipupini.proxy.ngrok.com`. This is likely to change.
