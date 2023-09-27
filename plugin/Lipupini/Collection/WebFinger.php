<?php

/*
The WebFinger plugin should serve the following request for valid local collection folders:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class WebFinger extends Plugin {
	public function start(State $state): State {
		if (preg_match('#^/\.well-known/webfinger\?resource=acct(?::|%3A%40)(.*)$#', $_SERVER['REQUEST_URI'], $matches)) {
			$identifier = $matches[1];

			// Webfinger URL may come in URL encoded
			if (!str_contains($identifier, '@')) {
				$identifier = urldecode($identifier);
			}

			Lipupini::validateCollectionFolderName(collectionFolderName: $identifier);

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

			$state->lipupiniMethod = 'shutdown';
		}

		return $state;
	}
}
