Namespaced plugins are stored here.

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

namespace Plugin\MyPluginNamespace;

use System\Plugin;
use System\Lipupini;

class MyPluginThatNeedsARoute extends Plugin {
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

Then in `index.php`:

```php
return (new Lipupini($state))
	->addPlugin(\Plugin\Lipupini\HomepageHtml::class)
	->addPlugin(\Plugin\MyPluginNamespace\MyPluginThatNeedsARoute::class)
	->addPlugin(\Plugin\Lipupini\Collection\WebFinger::class)
	->addPlugin(\Plugin\Lipupini\Collection\Url::class)
	->addPlugin(\Plugin\Lipupini\Collection\Avatar::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\Html::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\Atom::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\ActivityPubJson::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Image::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Video::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Audio::class)
	->start();

```
