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
		if (preg_match('#^/@([^/]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			$account = $matches[1];

			if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
				$split = explode(Lipupini::formatAndRequireAccount($account), '@');
				$username = $split[0];
				$host = $split[1];
			} else {
				$username = $account;
				$host = HOST;
				$account = Lipupini::formatAndRequireAccount($username . '@' . $host);
			}

			$state += [
				'account' => [
					'username' => $username,
					'host' => $host,
					'address' => $account,
				]
			];
		}

		return $state;
	}
}
