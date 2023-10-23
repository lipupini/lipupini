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

		// Follow request debug
		// Switch to true to enable
		if (false && $_GET['ap'] === 'follow-debug') {
			$remote = 'dup@pix.dup.bz';

			$remoteActor = RemoteActor::fromHandle(
				handle: $remote,
				cacheDir: $this->system->dirStorage . '/cache/ap'
			);

			$sendToInbox = $remoteActor->getInboxUrl();

			if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
				throw new Exception('Could not determine inbox URL', 400);
			}

			// Create the JSON payload for the Follow activity (adjust as needed)
			$followActivity = [
				'@context' => 'https://www.w3.org/ns/activitystreams',
				'id' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile#follow/' . md5(rand(0, 1000000) . microtime(true)),
				'type' => 'Follow',
				'actor' => $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile',
				'object' => $remoteActor->getId(),
			];

			$activityJson = json_encode($followActivity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

			$test = Outgoing\Http::sendSigned(
				keyId: $this->system->baseUri . '@' . $this->collectionFolderName . '?ap=profile#main-key',
				privateKeyPem: file_get_contents($this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/.rsakey.private'),
				inboxUrl: $remoteActor->getInboxUrl(),
				body: $activityJson,
				headers: [
					'Content-type' => $this::$mimeType,
					'Accept' => $this::$mimeType,
					'User-agent' => $this->system->userAgent,
				]
			);

			var_dump($test);

			exit('test235234234234');
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
			new $activityPubRequestClass($this->system);
		} catch (Exception $e) {
			$this->system->responseContent = $e;
		}

		$this->system->shutdown = true;
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
