<?php

namespace Plugin\Lipupini;

use System\Plugin;

class CollectionHtml extends Plugin {
	public function start(array $state): array {
		if (empty($state['collectionDirectory'])) { // We should be able to assume this directory exists here
			return $state;
		}

		if (!ActivityPub::getClientAccept('html')) {
			return $state;
		}

		header('Content-type: text/html');

		echo '<div>Liputini</div>';

		return [...$state,
			'lipupini' => 'shutdown',
		];
	}
}
