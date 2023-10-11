<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini;

class Request extends Lipupini\Http\Request {
	public string $responseType = 'text/html';
	public string|null $collectionFolderName = null;

	public function initialize(): void {
		$collectionFolderName = $this->getCollectionFolderNameFromRequest();

		if ($collectionFolderName === false) {
			return;
		}

		$this->collectionFolderName = $collectionFolderName;

		// Every computer requesting collection HTML will need to explicitly accept "text/html"
		if (!$this->clientAcceptsMimeTypes([
			'text/html',
		])) {
			return;
		}

		exit('HTML with frontend ' . $this->system->frontendView);
	}

	public function validateCollectionFolderName($collectionFolderName): void {
		if (!is_dir($this->system->dirCollection . '/' . $collectionFolderName)) {
			throw new Exception('Could not find collection from identifier');
		}
	}

	protected function getCollectionFolderNameFromRequest() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '@')) {
			return false;
		}

		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@([^/?]+)' . '#', $_SERVER['REQUEST_URI'], $matches)) {
			return false;
		}

		$collectionFolderName = $matches[1];

		if (!$collectionFolderName || strlen($collectionFolderName) > 200) {
			throw new Exception('Suspicious collection identifier (E1)');
		}

		if (substr_count($collectionFolderName, '@')) {
			throw new Exception('Suspicious collection identifier (E2)');
		}

		$this->validateCollectionFolderName($collectionFolderName);

		return $collectionFolderName;
	}
}
