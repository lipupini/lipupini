<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Plugin;

class Url extends Plugin {
	public static function validateCollectionDirectory($collectionDir) {
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

	public function start(State $state): State {
		if (preg_match('#^/@([^/]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			self::validateCollectionDirectory($matches[1]);
			$state->collectionDirectory = $matches[1];
			$state->collectionUrl = 'https://' . HOST . '/@' . $matches[1];
		}

		return $state;
	}
}
