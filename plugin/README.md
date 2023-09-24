Namespaced plugins are stored here.

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
