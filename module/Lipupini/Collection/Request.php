<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public string $name = '';
	public string $folder = '';
	public string $file = '';

	public function initialize(): void {
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '[^/]+/([^/?]+)#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// This should never happen, though not sure if it's only the browser that will prevent it
		if (str_contains($_SERVER['REQUEST_URI'], '..')) {
			throw new Exception('Suspicious collection URL');
		}

		$collectionUtility = new Collection\Utility($this->system);
		// Collection has to actually exist
		$collectionUtility->validateCollectionName($matches[1]);
		$this->name = $matches[1];
		// Folder not have to be a file or directory in the collection -- Any folder path
		$this->folder = $this->getCollectionFolder($collectionUtility, $matches[0]);
	}

	protected function getCollectionFolder(Utility $collectionUtility, $fullPath) {
		$collectionFolder = rawurldecode(parse_url(
			preg_replace('#^' . preg_quote($fullPath) . '/?#', '', $_SERVER['REQUEST_URI']),
			PHP_URL_PATH
		) ?? '');

		// If a filename is requested, only consider the directory
		if (pathinfo($collectionFolder, PATHINFO_EXTENSION)) {
			$this->file = $collectionFolder;
			$collectionFolder = pathinfo($this->file, PATHINFO_DIRNAME);
		}

		return $collectionFolder;
	}
}
