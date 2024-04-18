<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;

class Profile extends Request {
	public function initialize(): void {
		if ($this->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$this->collectionNameFromSegment(2);

		$profileFile = $this->system->dirCollection . '/' . $this->collectionName . '/.lipupini/profile.json';
		$profileData = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : [];

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $this->system->baseUri . 'ap/' . $this->collectionName . '/profile',
			'type' => 'Person',
			'following' => $this->system->baseUri . 'ap/' . $this->collectionName . '/following',
			'followers' => $this->system->baseUri . 'ap/' . $this->collectionName . '/followers',
			'inbox' => $this->system->baseUri . 'ap/' . $this->collectionName . '/inbox',
			'outbox' => $this->system->baseUri . 'ap/' . $this->collectionName . '/outbox',
			'preferredUsername' => $this->collectionName,
			'name' => $this->collectionName,
			'summary' => $profileData['summary'] ?? '',
			'url' => $this->system->baseUri . '@' . $this->collectionName,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$this->system->baseUri . 'ap/' . $this->collectionName . '/profile#main-key',
				'owner' => $this->system->baseUri . 'ap/' . $this->collectionName . '/profile',
				'publicKeyPem' => file_get_contents($this->system->dirCollection . '/' . $this->collectionName . '/.lipupini/rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $this->system->staticMediaBaseUri . $this->collectionName . '/avatar.png',
			],
			'endpoints' => [
				'sharedInbox' => $this->system->baseUri . 'ap/' . $this->collectionName . '/sharedInbox',
			],
		];

		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
