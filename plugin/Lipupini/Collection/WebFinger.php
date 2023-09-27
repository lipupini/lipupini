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

			// Webfinger request could be URL encoded, but it should contain "@"
			if (!str_contains($identifier, '@')) {
				$identifier = urldecode($identifier);
			}

			$collectionFolderName = Lipupini::validateCollectionFolderName(collectionFolderName: $identifier, disallowHostForLocal: false);

			$jsonData = [
				'subject' => 'acct:' . $identifier,
				'aliases' => [
					'https://' . HOST . '/@' . $collectionFolderName,
				],
				'links' => [
					[
						'rel' => 'http://webfinger.net/rel/profile-page',
						'type' => 'text/html',
						'href' => 'https://' . HOST . '/@' . $collectionFolderName,
					],
					[
						'rel' => 'http://schemas.google.com/g/2010#updates-from',
						'type' => 'application/atom+xml',
						'href' => 'https://' . HOST . '/@' . $collectionFolderName,
					],
					[
						'rel' => 'self',
						'type' => 'application/activity+json',
						'href' => 'https://' . HOST . '/@' . $collectionFolderName,
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
