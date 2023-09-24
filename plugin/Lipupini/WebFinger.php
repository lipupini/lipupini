<?php

namespace Plugin\Lipupini;

use System\Plugin;

class WebFinger extends Plugin {
	public function start() {
		var_dump($_SERVER['REQUEST_URI']);
	}
}
