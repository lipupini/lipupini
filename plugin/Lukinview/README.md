`Lukinview` is a decoupled frontend plugin for Lupupini that ships by default.

The webserver's document root ("webroot") is contained here. You can symlink it elsewhere.

Using this pattern, things like `favicon.ico`, loaded plugins, and the entire frontend can be efficiently customized.

The `c` folder in `webroot` needs to be writeable as it will store cache:

```shell
cd plugin/Lukinview/webroot
chmod 755 c
```

To make a new frontend plugin, copy this plugin and use your new plugin's `webroot` for your webserver's document root.

You will need to update the `$systemState` in `index.php` to reflect your new plugin, for example if the name is `Mokuview`:

```php
$systemState = new Plugin\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Mokuview',
	debug: true
);
```

Changing the document root will also rebuild any media file cache, if there is any. Delete or move the cache in the previous `webroot` to save space:

```shell
cd path/to/project/root
rm -rf plugin/Lukinview/webroot/c/*
```
