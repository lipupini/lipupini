<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public static string $mimeType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	public function initialize(): void {
		if (empty($this->system->request[Collection\Request::class]->name)) {
			return;
		}

		if (preg_match('#^/ap/[^/]+[/?]?$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)) {
			http_response_code(302);
			$this->system->responseContent = '302 Found';
			return;
		}

		if (!preg_match('#^/ap/[^/]+/(.*)$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)) {
			return;
		}

		$activityPubRequest = ucfirst($matches[1]);

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
