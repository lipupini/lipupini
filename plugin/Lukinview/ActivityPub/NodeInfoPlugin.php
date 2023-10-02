<?php

namespace Plugin\Lukinview\ActivityPub;

use Plugin\Lipupini\Http;
use Plugin\Lipupini\State;
use System\Plugin;

class NodeInfoPlugin extends Plugin {
	public function start(State $state): State {
		if ($_SERVER['REQUEST_URI'] !== '/.well-known/nodeinfo') {
			return $state;
		}

		if (!Http::getClientAccept('ActivityPubJson')) {
			return $state;
		}

		$jsonData = [
			'version' => '2.0',
			'software' => [
				'name' => 'lipupini',
				'version' => '69.420',
			],
			'protocols' => [
				'activitypub'
			],
			'usage' => [
				'users' => [
					'total' => 420,
					'activeHalfYear' => 69,
					'activeMonth' => 0,
				],
				'localPosts' => 420,
				'localComments' => 69,
			],
			'openRegistrations' => 'false',
		];

		header('Content-type: application/json');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}
}
