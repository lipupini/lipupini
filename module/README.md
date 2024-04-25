Namespaced modules are stored here.

Default modules:

- [Lipupini](Lipupini) - Backend
- [Lukinview](Lukinview) - General frontend, suitable for visual artists / photographers, writers

While `Lukinview` can already support _any_ context with some creativity, other ideas include:

- `Mokuview` - Frontend for restaurants. You might subscribe to `@specials@crabshack.co` or `@menu@crabshack.co`
- `Kalamaview` - Frontend for musicians / bands. Could have collections like `@shows@artistname.music` or `@media@artistname.music`
- `Nimiview` - Frontend for writers / bloggers. You might subscribe to `@thoughts@writing.tld` or `@publications@writing.tld`
- Additional general frontends that explore various frontend tooling

A goal of Lipupini is to be very modular. Modules are hopefully as self-contained as possible. For example, the default modules each do their own routing if they need to read routes.

If you don't like the way something is implemented, you can change it with a module.

Modules can be extended, overridden, or swapped.

---

To extend the `module/Lipupini/WebFinger/Request` module, make a file `module/OtherNameSpace/WebFinger/Request.php`.

In `OtherNameSpace/WebFinger/Request.php`, use:

```php
namespace Module\OtherNameSpace\WebFinger;

use Module\Lipupini\WebFinger;

class Request extends WebFinger\Request {
	// This will override the `initialize()` method of the parent/extended WebFinger class
	public function initialize(): void {
		if (!str_starts_with($_SERVER['REQUEST_URI_DECODED'], $this->system->baseUriPath . '.well-known/webfinger')) {
			return false;
		}

		$this->system->responseType = 'application/json';
		$this->system->responseContent = json_encode(['response', 'data', 'here'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		$this->system->shutdown = true;
	}
}
```

Then in `system/config/state.php`, queue the `Module\OtherNameSpace\WebFinger\Request` module instead of the `Module\Lipupini\WebFinger\Request` module.

## Basic development guide

There are no real rules. The `.editorconfig` file outlines some formatting preferences, but you don't have to follow them if you have a different preference for your module. Please feel free to contribute in your own style. You can even ship a custom `.editorconfig` in your module with totally different preferences, and [most IDEs](https://editorconfig.org/#pre-installed) should be able to pick it up.

In most cases for errors, throw an exception that _extends_ `Module\Lipupini\Exception`, or throw `Module\Lipupini\Exception` itself, or your own Exception handler extending that one. This allows for some future extendability, but is not a hard rule for creating a PR either (there are no rules for creating a PR).

### Throwing a 404 error with some output

```php
http_response_code(404);
echo 'Not found';
exit();
```

Or via module syntax (recommended):

```php
http_response_code(404);
$this->system->responseContent 'Not found';
$this->system->shutdown = true;
return;
```

### Detecting a route from a module

If more complex route detection is needed, use `preg_match` instead of checking `$_SERVER['REQUEST_URI_DECODED']` directly. See [Lipupini/Collection/MediaProcessor/Request/AvatarRequest.php](Lipupini/Collection/MediaProcessor/Request/AvatarRequest.php) for one such example.

```php
<?php

namespace Module\MyNamespace\MyModule;

use Module\Lipupini\Request\Incoming\Http;

class HasARouteRequest extends Http {
	public function initialize(): void {
		if ($_SERVER['REQUEST_URI_DECODED'] !== '/myroute') {
			return;
		}

		$this->system->responseType = 'text/html';
		$this->system->responseContent = 'This is the route at "/myroute"';
		$this->system->shutdown = true;
	}
}
```

Then in `system/config/state.php` add `Module\MyNamespace\MyModule\HasARouteRequest`:

```php
return new Module\Lipupini\State(
	[...]
	request: [
		[...]
		Module\Lukinview\Request\Html\HomepageRequest::class => null,
		Module\MyNamespace\MyModule\HasARouteRequest::class => null, // Here is your new module
		Module\Lipupini\WebFinger\Request::class => null,
		Module\Lipupini\ActivityPub\NodeInfoRequest::class => null,
		[...]
	],
	[...]
);
```

Request modules are initialized in the order they are specified in `system/config/state.php`.

See [Module\Lipupini\Api\Request](Lipupini/Api/Request.php) for an example of extracting a collection name from a URL. For example, `mycollection` is derived from the 2nd path segment of URL `/api/mycollection/cat.jpg.json` and then available via `$this->collectionName`. Using `collectionNameFromSegment` is preferred because it is verified before it can be used.

```php
$this->collectionNameFromSegment(2);
```
