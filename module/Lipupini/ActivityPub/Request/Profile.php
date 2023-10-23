<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;

class Profile extends Request {
	public function initialize(): void {
		if ($this->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$profileFile = $this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/.profile.json';
		$profileData = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : [];

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile',
			'type' => 'Person',
			'following' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=following',
			'followers' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=followers',
			'inbox' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=inbox',
			'outbox' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=outbox',
			'preferredUsername' => $this->collectionFolderName,
			'name' => $this->collectionFolderName,
			'summary' => $profileData['summary'] ?? '',
			'url' => $this->system->baseUri . '@' . $this->collectionFolderName,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile#main-key',
				'owner' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile',
				'publicKeyPem' => file_get_contents($this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/.rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $this->system->baseUri . 'c/avatar/' . $this->collectionFolderName . '.png',
			],
			'endpoints' => [
				'sharedInbox' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=sharedInbox',
			]
		];

		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
