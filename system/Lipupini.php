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

	public static function validateCollectionFolderName(string $collectionFolderName, bool $disallowHostForLocal = true) {
		if (str_contains($collectionFolderName, '@')) {
			if (substr_count($collectionFolderName, '@') > 1) {
				throw new Exception('Invalid account identifier format (E1)');
			}

			if (!filter_var($collectionFolderName, FILTER_VALIDATE_EMAIL)) {
				throw new Exception('Invalid account identifier format (E2)');
			}

			$exploded = explode('@', $collectionFolderName);

			$username = $exploded[0];
			$host = $exploded[1];

			// `HOST` is from `system/Initialize.php` and refers to the current hostname
			if ($host === HOST)  {
				// For example, don't allow http://localhost/@example@localhost
				// because it would be a duplicate of http://localhost/@example
				if ($disallowHostForLocal === true) {
					http_response_code(404);
					throw new Exception('Invalid format for local account ');
				} else {
					$collectionFolderName = $username;
				}
			}
		}

		// Overwrite with full path
		$fullCollectionPath = DIR_COLLECTION . '/' . $collectionFolderName;

		if (
			!is_dir($fullCollectionPath)
		) {
			http_response_code(404);
			throw new Exception('Could not find account (E1)');
		}

		return $collectionFolderName;
	}
}
