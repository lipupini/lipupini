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
		$this->renderHtml($state);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}

	public function renderHtml(State $state) {
		require(__DIR__ . '/Html/Core/Open.php');
		require(__DIR__ . '/Html/Grid/Grid.php');
		echo '<script>let collectionData = ' . json_encode(Lipupini::getCollectionData($state->collectionFolderName), JSON_UNESCAPED_SLASHES) . '</script>';
		require(__DIR__ . '/Html/Grid/Footer.php');
		require(__DIR__ . '/Html/Core/Close.php');
	}
}
