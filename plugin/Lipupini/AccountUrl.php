<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini;

use System\Lipupini;
use System\Plugin;

class AccountUrl extends Plugin {
	public function start(array $state): array {
		if (preg_match('#^/@([^/]*)$#', $_SERVER['REQUEST_URI'], $matches)) {
			$account = $matches[1];

			if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
				$account = $account . '@' . HOST;
			}

			Lipupini::requireAccountExists($account);

			$state += [
				'account' => $account,
			];
		}

		return $state;
	}
}
