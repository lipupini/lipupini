<?php

namespace Plugin\Lukinview\ActivityPub;

use Plugin\Lipupini\Http;
use Plugin\Lipupini\State;
use System\Plugin;

class CollectionInfoPlugin extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (empty($state->collectionUrl)) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if ($state->collectionPath !== '') {
			return $state;
		}

		if (!Http::getClientAccept('ActivityPubJson')) {
			return $state;
		}

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams',
				[
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
					/*'alsoKnownAs' => [
						'@id' => 'as:alsoKnownAs',
						'@type' => '@id',
					],*/
					/*'movedTo' => [
						'@id' => 'as:movedTo',
						'@type' => '@id',

					],*/
				],
			],
			'id' => $state->collectionUrl,
			'type' => 'Person',
			'following' => $state->collectionUrl . '?following',
			'followers' => $state->collectionUrl . '?followers',
			'inbox' => $state->collectionUrl . '?inbox',
			'outbox' => $state->collectionUrl . '?outbox',
			'preferredUsername' => $state->collectionFolderName,
			'name' => $state->collectionFolderName,
			'summary' => null,
			'url' => $state->collectionUrl,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' => $state->collectionUrl . '#main-key',
				'owner' => $state->collectionUrl,
				'publicKeyPem' => file_get_contents(DIR_COLLECTION . '/' . $state->collectionFolderName . '/.lipupini/.rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => 'https://' . HOST . '/c/avatar/' . $state->collectionFolderName . '.png',
			],
			'endpoints' => [
				'sharedInbox' => '/?sharedInbox',
			]
		];

		header('Content-type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}
}
