Namespaced plugins are stored here.

Default plugins:

- [Lipupini](Lipupini) - Backend
- [Lukinview](Lukinview) - General frontend, suitable for visual artists / photographers, writers

While `Lukinview` can already support _any_ context with some creativity, other ideas include:

- `Mokuview` - Frontend for restaurants. You might subscribe to `@specials@crabshack.co` or `@menu@crabshack.co`
- `Kalamaview` - Frontend for musicians / bands. Could have collections like `@shows@artistname.music` or `@media@artistname.music`
- `Nimiview` - Frontend for writers / bloggers. You might subscribe to `@thoughts@writing.tld` or `@publications@writing.tld`
- Additional general frontends that explore various frontend tooling

A goal of Lipupini is to be very modular. Plugins are hopefully as self-contained as possible. For example, the default plugins each do their own routing if they need to read routes.

If you don't like the way something is implemented, you can change it with a plugin.

Plugins can be extended, overridden, or swapped.

---

To extend the `plugins/Lipupini/WebFinger/Request` plugin, make a file `plugins/OtherNameSpace/WebFinger/Request.php`.

In `OtherNameSpace/WebFinger/Request.php`, use:

```php
namespace Plugin\OtherNameSpace\WebFinger;

use System\Plugin;

class Request extends \Plugin\Lipupini\WebFinger\Request {
	// This will override the start() method of the parent/extended WebFinger class
	public function initialize(): void {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/webfinger')) {
			return false;
		}

		header('Content-type: application/json');
		$this->system->responseContent = json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		$this->system->shutdown = true;
	}
}
```

Then in `webroot/index.php`, queue the `Plugin\OtherNameSpace\WebFinger\Request` plugin instead of the `Plugin\Lipupini\WebFinger\Request` plugin.

## Basic development guide

There are no real rules. The `.editorconfig` file outlines some formatting preferences, but you don't have to follow them if you have a different preference for your plugin. Please feel free to contribute in your own style. You can even ship a custom `.editorconfig` in your plugin with totally different preferences, and [most IDEs](https://editorconfig.org/#pre-installed) should be able to pick it up.

In most cases for errors, throw an exception that _extends_ `System\Exception`, or throw `System\Exception`, or your own Exception handler extending that one. This allows for some future extendability, but is not a hard rule for creating a PR either (there are no rules for creating a PR).

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
$this->system->shutdown = true;
return;
```

### Detecting a route from a plugin

If more complex route detection is needed, use `preg_match` instead of checking `$_SERVER['REQUEST_URI']`. See [Lipupini/Collection/AvatarRequest.php](Lipupini/Collection/AvatarRequest.php) for one such example.

```php
<?php

namespace Plugin\MyNamespace\MyPlugin;

use Plugin\Lipupini\Http;
use System\Plugin;

class HasARouteRequest extends Http\Request {
	public function initialize(): void {
		if ($_SERVER['REQUEST_URI'] !== '/myroute') {
			return;
		}

		// Every computer requesting HTML will need to explicitly accept "text/html"?
		if (!$this->validateRequestMimeTypes('HTTP_ACCEPT', [
			'text/html',
		])) {
			return;
		}

		header('Content-type: text/html');
		echo 'This is the route at "/myroute"';

		$this->system->shutdown = true;
	}
}
```

Then in `index.php` add `Plugin\MyNamespace\MyPlugin\HasARouteRequest`:

```php
return (new System\Lipupini(
	$systemState
))->requestQueue([
	"Plugin\\{$systemState->frontendView}\\HomepageRequest",
	Plugin\Lipupini\WebFinger\Request::class,
	Plugin\Lipupini\ActivityPub\NodeInfoRequest::class,
	Plugin\Lipupini\Collection\FolderRequest::class,
	Plugin\MyNamespace\MyPlugin\HasARouteRequest::class, // Here is your new plugin
	Plugin\Lipupini\Collection\DocumentRequest::class,
	Plugin\Lipupini\Collection\AvatarRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\ImageRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\VideoRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\MarkdownRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\AudioRequest::class,
	Plugin\Lipupini\Rss\Request::class,
	Plugin\Lipupini\ActivityPub\Request::class,
])->render();
```

Request plugins are initialized in the order they are specified in `index.php`. Since your `MyPlugin\HasARouteRequest` comes after `Collection\FolderRequest`, it will have available to it the currently requested collection folder and collection path, if present, because those are initialized in the `Collection\FolderRequest` plugin.

See [Plugin\Lipupini\Rss\Request](Lipupini/Rss/Request.php) for an example of using the extracted variables. It should be best to use them in the same way, since they are sanitized and verified before they become available in the plugin state.
