<?php

namespace System;

// This should be sort of a middleware system that loads plugins from the `plugin` directory and queues them.
// Each plugin should extend this file, and can have a list of dependencies that this file can check in the loading process.
// Ultimately the order of loading is determined linearly in `webroot/index.php`.

abstract class Plugin {
	public function __construct() {
		if (LIPUPINI_DEBUG) {
			error_log('DEBUG: Starting ' . get_called_class());
		}
	}

	abstract public function start(array $state): array;
}
