<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Queued;

class Request extends Queued {
	public static string $mimeType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	use Collection\Trait\CollectionRequest;

	public function initialize(): void {
		if (!str_starts_with($_SERVER['REQUEST_URI_DECODED'], $this->system->baseUriPath . 'ap/')) return;
		$this->collectionNameFromSegment(2);

		$apRequestUri = preg_replace(
			'#^/ap/' . preg_quote($this->collectionName) . '/?#', '',
			parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH)
		);

		if (strpos($apRequestUri, '/')) {
			return;
		}

		// No action specified, only collection name
		if ($apRequestUri === '') {
			http_response_code(302);
			$this->system->responseContent = '302 Found';
			return;
		}

		$activityPubRequest = ucfirst($apRequestUri);

		// This will compute to a class in the `./Request` folder e.g. `./Request/Follow.php`;
		if (!class_exists($activityPubRequestClass = '\\Module\\Lipupini\\ActivityPub\\Request\\' . $activityPubRequest)) {
			throw new Exception('Invalid ActivityPub request');
		}

		if ($this->system->debug) {
			error_log('DEBUG: Performing ActivityPub request "' . $activityPubRequest . '"');
		}

		$this->system->responseType = static::$mimeType;
		try {
			// `responseContent` should be set in the `$activityPubRequestClass`
			new $activityPubRequestClass($this->system);
		} catch (Exception $e) {
			$this->system->responseContent = $e;
		}
		$this->system->shutdown = true;
	}
}
