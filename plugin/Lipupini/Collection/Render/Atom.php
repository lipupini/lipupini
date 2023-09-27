<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Atom extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('AtomXML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if ($state->collectionPath !== '') {
			return $state;
		}

		// @TODO: Implement `application/atom+xml` feed for profile

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}
}
