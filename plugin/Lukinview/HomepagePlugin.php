<?php

namespace Plugin\Lukinview;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class HomepagePlugin extends Plugin {
	public string $pageTitle = 'Homepage@' . HOST;

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
		require(__DIR__ . '/Html/Homepage.php');
		require(__DIR__ . '/Html/Core/Close.php');
	}

	public function getLocalCollections() {
		$dir = new \DirectoryIterator(DIR_COLLECTION);
		$localCollections = [];
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDir() || $fileinfo->isDot()) {
				continue;
			}

			if (!is_dir($fileinfo->getPathname() . '/.lipupini')) {
				continue;
			}

			$localCollections[] = $fileinfo->getFilename();
		}

		return $localCollections;
	}
}
