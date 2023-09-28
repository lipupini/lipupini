<?php

namespace Plugin\Lipupini;

use System\Plugin;
use System\Lipupini;

class HomepageHtml extends Plugin {
	public function start(State $state): State {
		if ($_SERVER['REQUEST_URI'] !== '/') {
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		header('Content-type: text/html');
		$this->renderHtml($state);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}

	public function renderHtml(State $state) {
		require(__DIR__ . '/Html/Core/Open.php');
		echo '<h1>Lipupini</h1><ul>';

		foreach ($this->getLocalCollections() as $localCollection) {
		  echo '<li><a href="/@' . htmlentities($localCollection) . '">' . htmlentities($localCollection) . '</a></li>';
		}

		echo '</ul>';

		require(__DIR__ . '/Html/Core/Close.php');
	}

	public function getLocalCollections() {
		$dir = new \DirectoryIterator(DIR_COLLECTION);
		$localCollections = [];
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDir() || $fileinfo->isDot()) {
				continue;
			}

			$localCollections[] = $fileinfo->getFilename();
		}

		return $localCollections;
	}
}
