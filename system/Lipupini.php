<?php

namespace System;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;

class Lipupini {
	private array $plugins = [];

	public function __construct(private State $state) { }

	public function addPlugin($class) {
		$this->plugins[] = $class;
		return $this;
	}

	// Start Lipupini
	public function start() {
		if (
			// Using PHP's builtin webserver, this will return a static file (e.g. CSS, JS, image) if it exists at the requested path
			php_sapi_name() === 'cli-server' &&
			$_SERVER['PHP_SELF'] !== '/index.php' &&
			file_exists(DIR_WEBROOT . $_SERVER['PHP_SELF'])
		) {
			return false;
		}

		// Loop through all queued plugin classes
		foreach ($this->plugins as $plugin) {
			// Create an instance of the next plugin
			$pluginInstance = new $plugin;
			// Start the next plugin, passing in State and returning optionally updated State
			$this->state = $pluginInstance->start($this->state);

			/*
			If the State's 'lipupiniMethod' comes back from a plugin with a value, it can contain a method
			from this class which will be run before the next plugin is started. For example, a plugin can
			return `$state->lipupiniMethod === 'shutdown'` and `$this->shutdown()` method will be called.
			*/
			if (
				!empty($this->state->lipupiniMethod) &&
				method_exists($this, $this->state->lipupiniMethod)
			) {
				$this->{$this->state->lipupiniMethod}();
			}
		}

		http_response_code(404);
		echo 'Not found';

		$this->shutdown();
	}

	public function shutdown(): void {
		exit();
	}
}
