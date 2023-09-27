<?php

/*
The WebFinger plugin should serve the `collection/user@domain.tld/.lipupini/.webfinger.json` file to:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini;

use System\Plugin;

class WebFinger extends Plugin {
	public function start(array $state): array {
		if (preg_match('#^/\.well-known/webfinger\?resource=acct(?::|%3A%40)(.*)$#', $_SERVER['REQUEST_URI'], $matches)) {
			$identifier = $matches[1];

			if (!str_contains($identifier, '@') || substr_count($identifier, '@') > 1) {
				http_response_code(404);
				throw new Exception('Invalid collection identifier for WebFinger (E1)');
			}

			if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
				http_response_code(404);
				throw new Exception('Invalid collection identifier for WebFinger (E2)');
			}

			$exploded = explode('@', $identifier);

			$username = $exploded[0];
			$host = $exploded[1];

			// `HOST` is from `system/Initialize.php`
			if ($host !== HOST)  {
				http_response_code(404);
				throw new Exception('Does not appear to be a local account');
			}

			$fullCollectionPath = DIR_COLLECTION . '/' . $username;

			if (
				!is_dir($fullCollectionPath)
			) {
				http_response_code(404);
				throw new Exception('Could not find account (E2)');
			}

			$jsonData = [
				'subject' => 'acct:' . $identifier,
				'aliases' => [
					'https://' . HOST . '/@' . $username,
				],
				'links' => [
					[
						'rel' => 'http://webfinger.net/rel/profile-page',
						'type' => 'text/html',
						'href' => 'https://' . HOST . '/@' . $username,
					],
					[
						'rel' => 'http://schemas.google.com/g/2010#updates-from',
						'type' => 'application/atom+xml',
						'href' => 'https://' . HOST . '/@' . $username,
					],
					[
						'rel' => 'self',
						'type' => 'application/activity+json',
						'href' => 'https://' . HOST . '/@' . $username,
					]
				]
			];

			header('Content-type: application/jrd+json');
			echo json_encode($jsonData);

			$state = [...$state, [
				'lipupini' => 'shutdown',
			]];
		}

		return $state;
	}
}
