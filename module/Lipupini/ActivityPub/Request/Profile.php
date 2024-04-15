<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;
use Module\Lipupini\Collection;

class Profile extends Request {
	public function initialize(): void {
		if ($this->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$collectionName = $this->system->request[Collection\Request::class]->name;

		$profileFile = $this->system->dirCollection . '/' . $collectionName . '/.lipupini/profile.json';
		$profileData = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : [];

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $this->system->baseUri . 'ap/' . $collectionName . '/profile',
			'type' => 'Person',
			'following' => $this->system->baseUri . 'ap/' . $collectionName . '/following',
			'followers' => $this->system->baseUri . 'ap/' . $collectionName . '/followers',
			'inbox' => $this->system->baseUri . 'ap/' . $collectionName . '/inbox',
			'outbox' => $this->system->baseUri . 'ap/' . $collectionName . '/outbox',
			'preferredUsername' => $collectionName,
			'name' => $collectionName,
			'summary' => $profileData['summary'] ?? '',
			'url' => $this->system->baseUri . '@' . $collectionName,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$this->system->baseUri . 'ap/' . $collectionName . '/profile#main-key',
				'owner' => $this->system->baseUri . 'ap/' . $collectionName . '/profile',
				'publicKeyPem' => file_get_contents($this->system->dirCollection . '/' . $collectionName . '/.lipupini/rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $this->system->staticMediaBaseUri . $collectionName . '/avatar.png',
			],
			'endpoints' => [
				'sharedInbox' => $this->system->baseUri . 'ap/' . $collectionName . '/sharedInbox',
			],
		];

		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
