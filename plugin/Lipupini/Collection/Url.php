<?php

/*
The Url plugin should be able to parse a request that starts with an "@".
For example: http://localhost/@example
An error will be thrown if there is not a corresponding directory in `collection`
After this URL is detected and validated, the collection directory is added
to State and available for subsequent plugins
*/

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Plugin;

class Url extends Plugin {
	public function start(State $state): State {
		if (preg_match('#^/@([^/?]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			self::validatecollectionFolderName($matches[1]);
			$state->collectionFolderName = $matches[1];
			$state->collectionUrl = 'https://' . HOST . '/@' . $matches[1];
		}

		return $state;
	}

	public static function validatecollectionFolderName($collectionDir) {
		if (str_contains($collectionDir, '@')) {
			if (substr_count($collectionDir, '@') > 1) {
				throw new Exception('Invalid account identifier format (E1)');
			}

			if (!filter_var($collectionDir, FILTER_VALIDATE_EMAIL)) {
				throw new Exception('Invalid account identifier format (E2)');
			}
		}

		// Overwrite with full path
		$fullCollectionPath = DIR_COLLECTION . '/' . $collectionDir;

		if (
			!is_dir($fullCollectionPath)
		) {
			http_response_code(404);
			throw new Exception('Could not find account (E1)');
		}

		return true;
	}
}
