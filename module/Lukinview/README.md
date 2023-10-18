`Lukinview` is a decoupled frontend module for Lupupini that ships by default.

The webserver's document root ("webroot") is contained here. You can symlink it elsewhere.

Using this pattern, things like `favicon.ico`, loaded modules, and the entire frontend can be efficiently customized.

The `c` folder in `webroot` needs to be writeable as it will store cache:

```shell
cd module/Lukinview/webroot
chmod 755 c
```

To make a new frontend module, copy this module and use your new module's `webroot` for your webserver's document root.

You will need to update the `$systemState` in `index.php` to reflect your new module, for example if the name is `Mokuview`:

```php
$systemState = new Module\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Mokuview',
	debug: true
);
```

Changing the document root will also rebuild any media file cache, if there is any. Delete or move the cache in the previous `webroot` to save space:

```shell
cd path/to/project/root
rm -rf module/Lukinview/webroot/c/*
```
