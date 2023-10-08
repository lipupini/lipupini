<?php

/*
The WebFinger plugin should serve the following request for valid local collection folders:
https://domain.tld/.well-known/webfinger?resource=acct:user%40domain.org
*/

namespace Plugin\Lukinview\Collection;

use Plugin\Lipupini\Collection;
use Plugin\Lipupini\Exception;
use Plugin\Lipupini\Http;
use Plugin\Lipupini\State;
use System\Plugin;

class WebFingerPlugin extends Plugin {
	public function start(State $state): State {
		if (!preg_match('#^/\.well-known/webfinger\?resource=acct(?::|%3A)(.+)$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		if (!Http::getClientAccept('WebFingerJson')) {
			return $state;
		}

		$collectionFolderName = $matches[1];

		// Webfinger request could be URL encoded, but it should contain "@"
		if (!str_contains($collectionFolderName, '@')) {
			$collectionFolderName = urldecode($collectionFolderName);
		}

		if ($collectionFolderName[0] === '@') {
			$collectionFolderName = preg_replace('#^@#', '', $collectionFolderName);
		}

		if (!$collectionFolderName) {
			throw new Exception('Could not determine collection');
		}

		$collectionFolderName = Collection\Utility::validateCollectionFolderName(collectionFolderName: $collectionFolderName, disallowHostForLocal: false);

		$jsonData = [
			'subject' => 'acct:' . $collectionFolderName,
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
				],
				[
					'rel' => 'http://webfinger.net/rel/avatar',
					'type' => 'image/png',
					'href' => 'https://' . HOST . '/c/avatar/' . $collectionFolderName . '.png',
				],
			]
		];

		header('Content-type: application/jrd+json');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}
}
