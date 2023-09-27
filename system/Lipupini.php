<?php

namespace System;

// This should be sort of a middleware system that loads plugins from the `plugin` directory and queues them.
// Each plugin should extend this file, and can have a list of dependencies that this file can check in the loading process.
// Ultimately the order of loading is determined linearly in `webroot/index.php`.

class Lipupini {
	private array $plugins = [];

	public function __construct(private array $state = []) { }

	public function addPlugin($class) {
		$this->plugins[] = $class;
		return $this;
	}

	public function start() {
		foreach ($this->plugins as $plugin) {
			$pluginInstance = new $plugin;
			$this->state = $pluginInstance->start($this->state);

			// If there is a key called 'lipupini', it can contain a method from this class that can be run after the plugin is finished
			// For example, a plugin can return ['lipupini' => 'shutdown'] and the shutdown() method will be called
			if (
				!empty($this->state['lipupini']) &&
				method_exists($this, $this->state['lipupini'])
			) {
				$this->{$this->state['lipupini']}();
			}
		}

		if (
			// Using PHP's builtin webserver, this will return a static file (e.g. CSS or JS) if it exists at the requested path
			php_sapi_name() === 'cli-server' &&
			$_SERVER['PHP_SELF'] !== '/index.php' &&
			file_exists(DIR_WEBROOT . $_SERVER['PHP_SELF'])
		) {
			return false;
		}

		http_response_code(404);
		echo 'Not found';

		$this->shutdown();
	}

	public function shutdown(): void {
		exit();
	}
}
