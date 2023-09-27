<?php

namespace Plugin\Lipupini\Collection\Render;

use System\Lipupini;
use System\Plugin;

class Atom extends Plugin {
	public function start(array $state): array {
		if (empty($state['collectionDirectory'])) { // We should be able to assume this directory exists here
			return $state;
		}

		if (!Lipupini::getClientAccept('AtomXML')) {
			return $state;
		}

		// @TODO: Implement `application/atom+xml` feed for profile

		return [...$state,
			'lipupini' => 'shutdown',
		];
	}
}
