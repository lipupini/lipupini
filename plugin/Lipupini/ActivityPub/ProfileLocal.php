<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini\ActivityPub;

use System\Exception;
use System\Plugin;

class ProfileLocal extends Plugin {
	public function start(array $state): array {
		$pluginAcceptsMimes = [
			'application/json',
			'application/activity+json',
			'application/ld+json',
			'application/ld+json; profile="https://www.w3.org/ns/activitystreams',
		];

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		$matchedMime = false;

		foreach ($clientAcceptsMimes as $mime) {
			if (in_array($mime, $pluginAcceptsMimes, true)) {
				$matchedMime = true;
				break;
			}
		}

		if (!$matchedMime) {
			return $state;
		}

		if (empty($state['account'])) {
			return $state;
		}

		if (preg_match('#^/@' . $state['account']['username'] . '/?$#', $_SERVER['REQUEST_URI'])) {
			$profileJson = DIR_COLLECTION . '/' . $state['account']['address'] . '/' . DIR_DOT . '/.profile.json';

			if (
				!file_exists($profileJson)
			) {
				http_response_code(404);
				throw new Exception('Could not find profile info');
			}

			header('Content-type: application/activity+json');
			echo file_get_contents($profileJson);

			$state += [
				'lipupini' => 'shutdown',
			];
		}

		return $state;
	}
}
