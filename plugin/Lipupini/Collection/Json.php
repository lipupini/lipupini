<?php

namespace Plugin\Lipupini\Collection;

use System\Plugin;

use Plugin\Lipupini\ActivityPub;

class Json extends Plugin {
	public function start(array $state): array {
		if (empty($state['collectionDirectory'])) { // We should be able to assume this directory exists here
			return $state;
		}

		if (empty($state['collectionRootUrl'])) {
			return $state;
		}

		if (!ActivityPub::getClientAccept('json')) {
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
			'id' => $state['collectionRootUrl'],
			'type' => 'Person',
			'following' => $state['collectionRootUrl'] . '/following',
			'followers' => $state['collectionRootUrl'] . '/followers',
			'inbox' => $state['collectionRootUrl'] . '/inbox',
			'outbox' => $state['collectionRootUrl'] . '/outbox',
			'preferredUsername' => $state['collectionDirectory'],
			'name' => $state['collectionDirectory'],
			'summary' => null,
			'url' => $state['collectionRootUrl'],
			'manuallyApprovesFollowers' => true,
			'publicKey' => [
				'id' => $state['collectionRootUrl'] . '#main-key',
				'owner' => $state['collectionRootUrl']
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => 'https://' . HOST . '/c/avatar/' . $state['collectionDirectory'] . '.png',
			],
			'endpoints' => [
				'sharedInbox' => $state['collectionRootUrl'] . '/fuck',
			]
		];

		header('Content-type: application/activity+json');
		echo json_encode($jsonData);

		return [...$state,
			'lipupini' => 'shutdown',
		];
	}
}
