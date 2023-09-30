`Lukinview` is a decoupled frontend plugin for Lupupini that ships by default.

The webserver's document root ("webroot") is contained here and symlinked to project root.

Using this pattern, things like `favicon.ico`, loaded plugins, and the entire frontend can be efficiently customized. You can "hotswap" entire frontends by changing the symlink without restarting the webserver.

The `c` folder in `webroot` needs to be writeable as it will store cache:

```shell
cd plugin/Lukinview/webroot
chmod 755 c
```

To make a new frontend, copy this plugin and change the symlink. For example, to use plugin `Mokuview`'s document root:

```php
cd path/to/project/root
ln -sf plugin/Mokuview/webroot webroot
```

A drawback is that changing the document root will also rebuild any media file cache, if there is any already. Delete or move the cache in the previous `webroot` to save space:

```php
cd path/to/project/root
rm -rf plugin/Lukinview/webroot/c/*
```
