<?php

namespace System;

// This should be sort of a middleware system that loads plugins from the `plugin` directory and queues them.
// Ultimately the order of loading is determined linearly in a Plugin's `webroot/index.php`.

use Plugin\Lipupini\State;

abstract class Plugin {
	public function __construct() {
		if (LIPUPINI_DEBUG) {
			error_log('DEBUG: Starting ' . get_called_class());
		}
	}

	abstract public function start(State $state): State;
}
