`Lukinview` is a decoupled frontend module for Lupupini that ships by default.

The webserver's document root (`webroot`) is contained here. You can symlink it elsewhere.

Using this pattern, things like `favicon.ico`, loaded modules, and the entire frontend can be efficiently customized.

The `c` folder in `webroot` needs to be writeable as it will store cache. This should be performed as the webserver user. If the `c` folder is already there, then it was writeable and this step can be skipped.

```shell
cd module/Lukinview/webroot
mkdir c || chmod 755 c
```

To make a new frontend module, copy this module and use your new module's `webroot` for your webserver's document root.

You will need to update `config/system.php` to reflect your new module, for example if the name is `Mokuview`:

```php
return new Module\Lipupini\State(
	[...]
	frontendView: 'Mokuview',
	[...]
);
```

Changing the document root will also rebuild any media file cache, if there is any. Delete or move the cache in the previous `webroot` to save space:

```shell
cd path/to/project/root
rm -rf module/Lukinview/webroot/c/*
```
