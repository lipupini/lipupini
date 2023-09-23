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
