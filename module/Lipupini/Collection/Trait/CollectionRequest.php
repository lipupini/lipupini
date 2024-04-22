<?php

namespace Module\Lipupini\Collection\Trait;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\Utility;

trait CollectionRequest {
	public string $collectionName;

	public function collectionNameFromSegment(int $segmentIndex, string $prefix = '', string $baseUri = null) {
		if (!$baseUri) $baseUri = $this->system->baseUriPath;
		$uri = preg_replace('#^' . preg_quote($baseUri) . '#', '', parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH));
		$segments = explode('/', $uri);
		$segmentIndex--;
		if (empty($segments[$segmentIndex])) return false;
		if ($prefix) {
			if (!str_starts_with($segments[$segmentIndex], $prefix)) {
				return false;
			}
			$collectionName = preg_replace('#^' . $prefix . '#', '', $segments[$segmentIndex]);
		} else {
			$collectionName = $segments[$segmentIndex];
		}

		// Not sure if it is only the browser that will prevent this type of breach
		if (str_contains($_SERVER['REQUEST_URI_DECODED'], '..')) {
			throw new Collection\Exception('Suspicious collection URL');
		}

		(new Utility($this->system))->validateCollectionName($collectionName);

		return $this->collectionName = $collectionName;
	}
}
