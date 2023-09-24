<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini;

use System\Exception;
use System\Lipupini;
use System\Plugin;

class WebFinger extends Plugin {
	public function start(array $state): array {
		if (preg_match('#^/\.well-known/webfinger\?resource=acct(?::|%3A%40)(.*)$#', $_SERVER['REQUEST_URI'], $matches)) {
			$account = $matches[1];

			$account = Lipupini::formatAndRequireAccount($account);

			$webfingerJson = DIR_COLLECTION . '/' . $account . '/' . DIR_DOT . '/.webfinger.json';

			if (
				!file_exists($webfingerJson)
			) {
				http_response_code(404);
				throw new Exception('Could not find account info');
			}

			header('Content-type: application/jrd+json');
			echo file_get_contents($webfingerJson);

			$state += [
				'lipupini' => 'shutdown',
			];
		}

		return $state;
	}
}
