Namespaced plugins are stored here.

Plugins can be extended, overridden, or swapped.

---

To extend the `plugins/Lipupini/WebFinger` plugin, make a folder `plugins/OtherNameSpace/WebFinger`.

In `OtherNameSpace/WebFinger/WebFinger.php`, use:

```php
<?php

namespace Plugin\OtherNameSpace;

use Plugin\Lipupini\WebFinger as LipupiniWebFinger;

class WebFinger extends LipupiniWebFinger {

}
```

Then in `webroot/index.php`, queue the `OtherNameSpace\WebFinger` plugin instead of the `Lipupini\WebFinger` plugin.
