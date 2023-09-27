<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini;

use Plugin\Lipupini\Exception;
use System\Plugin;

class LoadCollectionUrl extends Plugin {
	public function start(array $state): array {
		if (preg_match('#^/@([^/]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			$account = $matches[1];

			if (str_contains($account, '@')) {
				if (substr_count($account, '@') > 1) {
					throw new Exception('Invalid account identifier format (E1)');
				}

				if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
					throw new Exception('Invalid account identifier format (E2)');
				}
			}

			$collectionDir = DIR_COLLECTION . '/' . $account;

			if (
				!is_dir($collectionDir)
			) {
				http_response_code(404);
				throw new Exception('Could not find account');
			}

			$state += [
				'collectionDirectory' => $collectionDir,
				'collectionRootUrl' => 'https://' . HOST . '/@' . $collectionDir,
			];
		}

		return $state;
	}
}
