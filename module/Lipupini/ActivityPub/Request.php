<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public static string $mimeType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	public function initialize(): void {
		if (empty($this->system->request[Collection\Request::class]->folderName)) {
			return;
		}

		if (empty($_GET['ap'])) {
			return;
		}

		$activityPubRequest = ucfirst($_GET['ap']);

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
