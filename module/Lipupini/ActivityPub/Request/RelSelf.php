<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;

class RelSelf {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
			'type' => 'Person',
			'following' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=following',
			'followers' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=followers',
			'inbox' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=inbox',
			'outbox' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=outbox',
			'preferredUsername' => $activityPubRequest->collectionFolderName,
			'name' => $activityPubRequest->collectionFolderName,
			'summary' => null,
			'url' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '#main-key',
				'owner' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
				'publicKeyPem' => file_get_contents($activityPubRequest->system->dirCollection . '/' . $activityPubRequest->collectionFolderName . '/.lipupini/.rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $activityPubRequest->system->baseUri . 'c/avatar/' . $activityPubRequest->collectionFolderName . '.png',
			],
			'endpoints' => [
				'sharedInbox' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=sharedInbox',
			]
		];

		$activityPubRequest->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
