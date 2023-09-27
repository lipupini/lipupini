<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\ActivityPub;
use System\Plugin;

class Atom extends Plugin {
	public function start(array $state): array {
		if (empty($state['collectionDirectory'])) { // We should be able to assume this directory exists here
			return $state;
		}

		if (!ActivityPub::getClientAccept('Atom')) {
			return $state;
		}

		// @TODO: Implement `application/atom+xml` feed for profile

		return [...$state,
			'lipupini' => 'shutdown',
		];
	}
}
