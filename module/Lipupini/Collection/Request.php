<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public string $name = '';
	public string $folder = '';
	public string $file = '';

	public function initialize(): void {
		if (
			// E.g. `/@example`
			!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@([^.][^/]*)/?#', $_SERVER['REQUEST_URI'], $matches) &&
			// E.g. `/api/example` or `/rss/example`
			!preg_match('#^' . preg_quote($this->system->baseUriPath) . '[^/]+/([^/?]+)#', $_SERVER['REQUEST_URI'], $matches)
		) {
			return;
		}

		// Not sure if it is only the browser that will prevent this type of breach
		if (str_contains($_SERVER['REQUEST_URI'], '..')) {
			throw new Exception('Suspicious collection URL');
		}

		$collectionUtility = new Collection\Utility($this->system);
		// Collection has to actually exist
		$collectionUtility->validateCollectionName($matches[1]);
		$this->name = $matches[1];

		$collectionFolder = rawurldecode(preg_replace('#^' . preg_quote($matches[0]) . '/?#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

		// If a filename is being requested, store the filename in addition to the folder
		if (pathinfo($collectionFolder, PATHINFO_EXTENSION)) {
			$this->file = $collectionFolder;
			$collectionFolder = pathinfo($this->file, PATHINFO_DIRNAME);
		}

		// Folder not have to be a file or directory in the collection -- Any folder path
		$this->folder = $collectionFolder;
	}
}
