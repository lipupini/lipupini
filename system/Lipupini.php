<?php

namespace System;

use Plugin\Lipupini\Exception;

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

	public static function getClientAccept($type) {
		switch ($type) {
			case 'HTML' :
				$relevantAcceptsMimes = [
					'text/html',
				];
				break;
			case 'ActivityPubJson' :
				$relevantAcceptsMimes = [
					'application/activity+json',
					'application/ld+json',
					'application/ld+json; profile="https://www.w3.org/ns/activitystreams',
				];
				break;
			case 'AtomXML' :
				$relevantAcceptsMimes = [
					'application/atom+xml',
				];
				break;
			default :
				throw new Exception('Unknown accept type');
		}

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		$matchedMime = false;

		foreach ($clientAcceptsMimes as $mime) {
			if (in_array($mime, $relevantAcceptsMimes, true)) {
				$matchedMime = true;
				break;
			}
		}

		return $matchedMime;
	}
}
