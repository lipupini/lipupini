Namespaced plugins are stored here.

Default plugins:

- `Lipupini` - Backend
- `Lukinview` - General frontend, suitable for visual artists / photographers, writers

Ideas:

- `Mokuview` - Frontend for restaurants
- `Kalamaview` - Frontend for musicians / bands
- `Nimiview` - Frontend for writers / bloggers
- Additional general frontends that explore various frontend tooling

A goal of Lipupini is to be very modular. Plugins are hopefully as self-contained as possible. For example, the default plugins each do their own routing if they need to read routes.

If you don't like the way something is implemented, you can change it with a plugin.

Plugins can be extended, overridden, or swapped.

---

To extend the `plugins/Lipupini/WebFinger` plugin, make a file `plugins/OtherNameSpace/WebFinger.php`.

In `OtherNameSpace/WebFinger.php`, use:

```php
namespace Plugin\OtherNameSpace;

use System\Plugin;

class WebFinger extends Plugin {
	// This will override the start() method of the parent/extended WebFinger class
	public function start(array $state): array {
		return $state;
	}
}
```

Then in `webroot/index.php`, queue the `OtherNameSpace\WebFinger` plugin instead of the `Lipupini\WebFinger` plugin.

## Basic development guide

There are no real rules. The `.editorconfig` file outlines some formatting preferences, but you don't have to follow them if you have a different preference for your plugin.

In most cases for errors, throw an Exception using `plugin\Lipupini\Exception`, or your own Exception handler extending that one. This allows for some future extendability.

### Throwing a 404 error with some output

```php
http_response_code(404);
echo 'Not found';
exit();
```

via plugin method syntax:

```php
http_response_code(404);
echo 'Not found';
$state->lipupiniMethod = 'shutdown';
return $state;
```

### Detecting a route from a plugin

```php
<?php

namespace Plugin\MyNamespace;

use System\Plugin;
use System\Lipupini;

class MyPluginNeedsARoutePlugin extends Plugin {
	public function start(State $state): State {
		if ($_SERVER['REQUEST_URI'] !== '/myroute') {
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		header('Content-type: text/html');
		echo 'This is the route at "/myroute"';

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}
}
```

Then in `index.php` add `Plugin\MyNamespace\MyPluginNeedsARoutePlugin`:

```php
return (new Lipupini($state))
	->addPlugin(\Plugin\Lukinview\HomepagePlugin::class)
	->addPlugin(\Plugin\MyNamespace\MyPluginNeedsARoutePlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\WebFingerPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\UrlPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\AvatarPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\HtmlPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\AtomPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\ActivityPubJsonPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\ImagePlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\VideoPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\AudioPlugin::class)
	->start();
```
