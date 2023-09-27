<?php

namespace Plugin\Lipupini\Collection\Render;

use System\Plugin;
use Plugin\Lipupini\ActivityPub;

class Html extends Plugin {
	public function start(array $state): array {
		if (empty($state['collectionDirectory'])) { // We should be able to assume this directory exists here
			return $state;
		}

		if (!ActivityPub::getClientAccept('HTML')) {
			return $state;
		}

		header('Content-type: text/html');

		$this->renderHtml();

		return [...$state,
			'lipupini' => 'shutdown',
		];
	}

	public function renderHtml() {
		require(__DIR__ . '/Html/Core/Open.php');
		echo '<div>Lipupini</div>';
		require(__DIR__ . '/Html/Core/Close.php');
	}
}
