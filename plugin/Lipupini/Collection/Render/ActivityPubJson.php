<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class ActivityPubJson extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionDirectory)) { // We should be able to assume this directory exists here
			return $state;
		}

		if (empty($state->collectionUrl)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('ActivityPubJson')) {
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
			'following' => $state->collectionUrl . '/following',
			'followers' => $state->collectionUrl . '/followers',
			'inbox' => $state->collectionUrl . '/inbox',
			'outbox' => $state->collectionUrl . '/outbox',
			'preferredUsername' => $state->collectionDirectory,
			'name' => $state->collectionDirectory,
			'summary' => null,
			'url' => $state->collectionUrl,
			'manuallyApprovesFollowers' => true,
			'publicKey' => [
				'id' => $state->collectionUrl . '#main-key',
				'owner' => $state->collectionUrl
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => 'https://' . HOST . '/c/avatar/' . $state->collectionDirectory . '.png',
			],
			'endpoints' => [
				'sharedInbox' => $state->collectionUrl . '/fuck',
			]
		];

		header('Content-type: application/activity+json');
		echo json_encode($jsonData);

		$state->lipupini = 'shutdown';
		return $state;
	}
}
