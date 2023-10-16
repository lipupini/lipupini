<?php

namespace Plugin\Lipupini\ActivityPub;

use ActivityPhp;
use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public string $responseType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';
	public string|null $collectionFolderName = null;
	public string|null $collectionRequestPath = '';

	public function initialize(): void {
		if (empty($this->system->requests[Collection\FolderRequest::class]->collectionFolderName)) {
			return;
		}

		$this->collectionFolderName = $this->system->requests[Collection\FolderRequest::class]->collectionFolderName;

		if (!empty($this->system->requests[Collection\FolderRequest::class]->collectionRequestPath)) {
			$this->collectionRequestPath = $this->system->requests[Collection\FolderRequest::class]->collectionRequestPath;
		}

		if (
			$_SERVER['REQUEST_METHOD'] === 'GET' &&
			!$this->validateRequestMimeTypes('HTTP_ACCEPT', $this->mimeTypes())
		) {
			return;
		} else if (
			$_SERVER['REQUEST_METHOD'] === 'POST' &&
			(
				!$this->validateRequestMimeTypes('CONTENT_TYPE', $this->mimeTypes()) &&
				!$this->validateRequestMimeTypes('HTTP_CONTENT_TYPE', $this->mimeTypes())
			)
		) {
			return;
		}

		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' initialize()');
		}

		$activityPubRequest = !empty($_GET['request']) ? ucfirst($_GET['request']) : 'RelSelf';

		// This will compute to a class in the `./Request` folder e.g. `./Request/Follow.php`;
		if (!class_exists($activityPubRequestClass = '\\Plugin\\Lipupini\\ActivityPub\\Request\\' . $activityPubRequest)) {
			throw new Exception('Invalid ActivityPub request');
		}

		if ($this->system->debug) {
			error_log('DEBUG: Performing ActivityPub request "' . $activityPubRequest . '"');
		}

		header('Content-type: ' . $this->responseType);
		new $activityPubRequestClass($this);
		$this->system->shutdown = true;
	}

	public function mimeTypes(): array {
		return [
			'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
			'application/activity+json',
			'application/ld+json',
			//$this->system->debug ? 'text/html' : null,
		];
	}

	public function activityPubServer() {
		return new ActivityPhp\Server([
			'cache' => [
				'enabled' => !$this->system->debug,
				'stream' => $this->system->dirCollection . '/.apcache',
			],
			'instance' => [
				'debug' => $this->system->debug,
				'types' => 'ignore',
			],
			'http' => [
				'timeout' => $this->system->debug ? 2 : 11,
				'agent' => $this->userAgent(),
			],
		]);
	}

	public function ping(string $host) : bool {
		exec('ping -c 1 ' . escapeshellarg($host), $output, $resultCode);
		return $resultCode === 0;
	}

	public function createSignedRequest(string $sendToInbox, string $activityJson) {
		$collectionFolderName = $this->system->requests[Collection\FolderRequest::class]->collectionFolderName;

		return Lipupini\Http\Signature::signedRequest(
			privateKeyPath: $this->system->dirCollection . '/' . $collectionFolderName . '/.lipupini/.rsakey.private',
			keyId: $this->system->baseUri . '@' . $collectionFolderName . '#main-key',
			url: $sendToInbox,
			body: $activityJson,
			extraHeaders: [
				'Content-type' => $this->mimeTypes()[0],
				'Accept' => $this->mimeTypes()[0],
				'User-Agent' => $this->userAgent(),
				'Host' => $this->system->host, // Host without port
			]
		);
	}

	public function userAgent() {
		return  '(Lipupini/69.420; +' . $this->system->baseUri . ')';
	}
}
