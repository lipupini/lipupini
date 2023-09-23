<?php

namespace System;

// This should be sort of a middleware system that loads plugins from the `plugin` directory and queues them.
// Each plugin should extend this file, and can have a list of dependencies that this file can check in the loading process.
// Ultimately the order of loading is determined linearly in `webroot/index.php`.

class Plugin {

}
