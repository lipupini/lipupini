<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Html extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if ($state->collectionPath !== '') {
			return $state;
		}

		header('Content-type: text/html');

		$this->renderHtml();

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}

	public function renderHtml() {
		require(__DIR__ . '/Html/Core/Open.php');
		echo '<div>Lipupini</div>';
		require(__DIR__ . '/Html/Core/Close.php');
	}
}
