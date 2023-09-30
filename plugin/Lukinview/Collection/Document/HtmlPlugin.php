<?php

namespace Plugin\Lukinview\Collection\Document;

use Plugin\Lipupini\Collection;
use Plugin\Lipupini\Exception;
use Plugin\Lipupini\Http;
use Plugin\Lipupini\State;
use System\Plugin;

class HtmlPlugin extends Plugin {
	public string|null $pageTitle = null;
	public string|null $htmlHead = null;

	private string|null $collectionPath = null;
	private string|null $collectionFolderName = null;
	private array|null $fileData = null;
	private string|null $parentPath = null;

	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (empty($state->collectionPath)) {
			return $state;
		}

		if (!Http::getClientAccept('HTML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example/memes/cat-computer.jpg.html
		// Does not apply to http://locahost/@example
		if (
			!pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) ||
			!preg_match('#\.(?:[^.]+)\.html$#', $_SERVER['REQUEST_URI'])
		) {
			return $state;
		}

		if ('/@' . $state->collectionFolderName . '/' . $state->collectionPath . '.html' !== $_SERVER['REQUEST_URI']) {
			throw new Exception('Unexpected path in request');
		}

		$state->lipupiniMethod = 'shutdown';

		if (!file_exists(DIR_COLLECTION . '/' . $state->collectionFolderName . '/' . $state->collectionPath)) {
			http_response_code(404);
			echo 'Not found';
			return $state;
		}

		header('Content-type: text/html');
		// If it's a document/file, e.g. http://locahost/@example/memes/cat-computer.jpg.html
		return $this->renderHtml($state);
	}

	public function renderHtml(State $state) {
		$this->loadViewData($state);

		require(__DIR__ . '/../../Html/Core/Open.php');
		require(__DIR__ . '/Html/Document.php');
		require(__DIR__ . '/../../Html/Core/Close.php');

		return $state;
	}

	private function loadViewData(State $state): void {
		$this->collectionFolderName = $state->collectionFolderName;
		$this->pageTitle = $state->collectionPath . '@' . $state->collectionFolderName . '@' . HOST;
		$this->collectionPath = $state->collectionPath;
		$collectionData = Collection\Utility::getCollectionData($state);
		if (array_key_exists($this->collectionPath, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionPath];
		} else {
			$this->fileData = [];
		}
		$this->parentPath = '@' . $state->collectionFolderName . '/' . dirname($state->collectionPath);
		$this->htmlHead = '<link rel="stylesheet" href="/css/Document.css">' . "\n";
	}
}
