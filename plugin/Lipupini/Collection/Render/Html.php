<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Html extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionDirectory)) { // We should be able to assume this directory exists here
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		header('Content-type: text/html');

		$this->renderHtml();

		$state->lipupini = 'shutdown';
		return $state;
	}

	public function renderHtml() {
		require(__DIR__ . '/Html/Core/Open.php');
		echo '<div>Lipupini</div>';
		require(__DIR__ . '/Html/Core/Close.php');
	}
}
