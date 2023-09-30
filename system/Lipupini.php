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

	public static function getClientAccept($type) {
		// HTTP Accept header needs to be preset to proceed
		if (empty($_SERVER['HTTP_ACCEPT'])) {
			return false;
		}

		$relevantAcceptsMimes = match ($type) {
			'HTML' => [
				'text/html',
			],
			'ActivityPubJson' => [
				'application/activity+json',
				'application/ld+json',
				'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
			],
			'AtomXML' => [
				'application/atom+xml',
			],
			default => throw new Exception('Unknown accept type'),
		};

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		if (count($clientAcceptsMimes) > 20) {
			throw new Exception('Suspicious number of client accept MIMEs');
		}

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
		if (!trim($collectionFolderName)) {
			throw new Exception('Invalid collection identifier format (E1)');
		}

		if (str_contains($collectionFolderName, '@')) {
			$len = strlen($collectionFolderName);
			if ($len > 250 || $len < 5) {
				throw new Exception('Suspicious collection identifier format (E1)');
			}

			// Change `@example@localhost` to `example@localhost`
			if (str_starts_with($collectionFolderName, '@')) {
				$collectionFolderName = substr($collectionFolderName, 1);
			}

			if (substr_count($collectionFolderName, '@') > 1) {
				throw new Exception('Invalid collection identifier format (E1)');
			}

			if (!filter_var($collectionFolderName, FILTER_VALIDATE_EMAIL)) {
				throw new Exception('Invalid collection identifier format (E2)');
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
					throw new Exception('Invalid format for local collection ');
				}

				$collectionFolderName = $username;
			}
		}

		// Overwrite with full path
		$fullCollectionPath = DIR_COLLECTION . '/' . $collectionFolderName;

		if (
			!is_dir($fullCollectionPath)
		) {
			http_response_code(404);
			throw new Exception('Could not find collection (E1)');
		}

		return $collectionFolderName;
	}

	public static function getCollectionData(State $state) {
		$collectionRootPath = DIR_COLLECTION . '/' . $state->collectionFolderName;
		// E.g. `$state->collectionPath` could be `memes/cats`, which would be relative to `$collectionRootPath`. '' keeps the root collection path
		$collectionRelativePath = $state->collectionPath ?: '';
		$collectionAbsolutePath = $collectionRelativePath ? $collectionRootPath . '/' . $collectionRelativePath : $collectionRootPath;

		$filesJsonPath = $collectionAbsolutePath . '/.lipupini/.files.json';
		if (!file_exists($filesJsonPath)) {
			throw new Exception('Could not find data');
		}
		$collectionData = json_decode(file_get_contents($filesJsonPath), true);
		$return = [];
		// Process collection data first, since it can determine the display order
		foreach ($collectionData as $filename => $fileData) {
			if ($collectionRelativePath) {
				$filename = $collectionRelativePath . '/' . $filename;
			}
			if (!file_exists($collectionRootPath . '/' . $filename)) {
				throw new Exception('Could not find file for entry in ' . $state->collectionFolderName . '/.lipupini/.files.json');
			}
			$return[$filename] = $fileData;
		}
		foreach (new \DirectoryIterator($collectionAbsolutePath) as $fileData) {
			if ($fileData->isDot() || $fileData->getFilename()[0] === '.') {
				continue;
			}
			$filePath = $collectionRelativePath ? $collectionRelativePath . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			// Get data from `$collectionData` if exists
			$return[$filePath] = array_key_exists($fileData->getFilename(), $collectionData) ? $collectionData[$fileData->getFilename()] : [];
		}
		return $return;
	}

	public static function getSearchData(State $state, $query) {
		if (!$query) {
			throw new Exception('No query specified');
		}

		$collectionRootPath = DIR_COLLECTION . '/' . $state->collectionFolderName;

		$searchesJsonPath = $collectionRootPath . '/.lipupini/.savedSearches.json';
		if (!file_exists($searchesJsonPath)) {
			throw new Exception('Could not find search data');
		}
		$searchData = json_decode(file_get_contents($searchesJsonPath), true);
		if (!array_key_exists($query, $searchData)) {
			throw new Exception('Could not find specified search');
		}
		return $searchData[$query];
	}
}
