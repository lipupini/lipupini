<?php

namespace Plugin\Lukinview;

use Plugin\Lipupini\Http;

class HomepageRequest extends Http\Request {
	public string $pageTitle = '';

	public function initialize(): void  {
		if ($_SERVER['REQUEST_URI'] !== '/') {
			return;
		}

		if (!$this->clientAcceptsMimeTypes(['text/html'])) {
			return;
		}

		$this->pageTitle = 'Homepage@' . $this->system->host;

		header('Content-type: text/html');
		$this->renderHtml();

		$this->system->shutdown = true;
	}

	public function renderHtml() {
		require(__DIR__ . '/Html/Core/Open.php');
		require(__DIR__ . '/Html/Homepage.php');
		require(__DIR__ . '/Html/Core/Close.php');
	}

	public function getLocalCollections() {
		$dir = new \DirectoryIterator($this->system->dirCollection);
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
