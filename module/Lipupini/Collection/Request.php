<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public string|null $folderName = null;
	public string|null $path = null;

	public function initialize(): void {
		$collectionFolderName = $this->getCollectionFolderNameFromRequest();

		if ($collectionFolderName === false) {
			return;
		}

		$this->folderName = $collectionFolderName;
		$this->path = $this->getCollectionRequestPath();
	}

	protected function getCollectionRequestPath() {
		return rawurldecode(parse_url(
			preg_replace('#^/@' . $this->folderName . '/?#', '', $_SERVER['REQUEST_URI']),
			PHP_URL_PATH
		) ?? '');
	}

	protected function getCollectionFolderNameFromRequest() {
		if (
			empty($_SERVER['REQUEST_URI']) ||
			!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '@')
		) {
			return false;
		}

		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@([^/?]+)' . '#', $_SERVER['REQUEST_URI'], $matches)) {
			return false;
		}

		// This should never happen, though not sure if it's only the browser that will prevent it
		if (str_contains($_SERVER['REQUEST_URI'], '..')) {
			throw new Exception('Suspicious collection URL');
		}

		$collectionFolderName = $matches[1];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		return $collectionFolderName;
	}
}
