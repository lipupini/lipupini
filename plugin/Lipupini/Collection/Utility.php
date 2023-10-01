<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;

class Utility {
	public static function validateCollectionFolderName(string $collectionFolderName, bool $disallowHostForLocal = true) {
		if (!trim($collectionFolderName)) {
			throw new Exception('Invalid collection identifier format (E1)');
		}

		if (str_contains($collectionFolderName, '..')) {
			throw new Exception('Suspicious collection folder name (E3)');
		}

		if (str_contains($collectionFolderName, '@')) {
			$len = strlen($collectionFolderName);
			if ($len > 250 || $len < 5) {
				throw new Exception('Suspicious collection folder format (E1)');
			}

			// Change `@example@localhost` to `example@localhost`
			if (str_starts_with($collectionFolderName, '@')) {
				$collectionFolderName = substr($collectionFolderName, 1);
			}

			if (substr_count($collectionFolderName, '@') > 1) {
				throw new Exception('Invalid collection folder format (E1)');
			}

			if (!filter_var($collectionFolderName, FILTER_VALIDATE_EMAIL)) {
				throw new Exception('Invalid collection folder format (E2)');
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
		// `$state->collectionPath` could be `memes/cats`, which would be relative to `$collectionRootPath`
		if ($state->collectionPath) {
			if (pathinfo($state->collectionPath, PATHINFO_EXTENSION)) {
				// `$state->collectionPath` could be a file: `memes/cats/cat123.jpg`
				$collectionRelativePath = dirname($state->collectionPath) === '.' ? '' : dirname($state->collectionPath);
			} else {
				// `$state->collectionPath` could be a directory: `memes/cats`
				$collectionRelativePath = $state->collectionPath;
			}
		} else {
			// This would be the root of the collection
			$collectionRelativePath = '';
		}
		$collectionAbsolutePath = $collectionRelativePath ? $collectionRootPath . '/' . $collectionRelativePath : $collectionRootPath;
		$return = $collectionData = [];
		$filesJsonPath = $collectionAbsolutePath . '/.lipupini/.files.json';
		if (file_exists($filesJsonPath)) {
			$collectionData = json_decode(file_get_contents($filesJsonPath), true);
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
