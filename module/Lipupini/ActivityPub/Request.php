<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;
use Module\Lipupini\Request\Outgoing;

class Request extends Http {
	public static string $mimeType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';
	public string|null $collectionFolderName = null;
	public string|null $collectionRequestPath = '';

	public function initialize(): void {
		if (empty($this->system->requests[Collection\Request::class]->folderName)) {
			return;
		}

		$this->collectionFolderName = $this->system->requests[Collection\Request::class]->folderName;

		if (!empty($this->system->requests[Collection\Request::class]->path)) {
			$this->collectionRequestPath = $this->system->requests[Collection\Request::class]->path;
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

		header('Content-type: ' . static::$mimeType);
		try {
			new $activityPubRequestClass();
		} catch (Exception $e) {
			$this->system->responseContent = $e;
		}
		$this->system->shutdown = true;
	}

	public function sendSigned(string $inboxUrl, string $activityJson) {
		$headers = Outgoing\Signature::sign(
			$this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile#main-key',
			file_get_contents($this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/.rsakey.private'),
			$inboxUrl,
			$activityJson, [
				'Content-type' => $this->mimeTypes()[0],
				'Accept' => $this->mimeTypes()[0],
				'User-agent' => $this->system->userAgent,
			]
		);

		return Outgoing\Http::post($inboxUrl, $activityJson, $headers);
	}

	public function mimeTypes(): array {
		return [
			'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
			'application/activity+json',
			'application/ld+json',
			'*/*',
		];
	}
}
