<?php

namespace Plugin\Lipupini\ActivityPub;

use ActivityPhp;
use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public string $responseType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	public function initialize(): void {
		if (empty($this->system->requests[Collection\Request::class]->collectionFolderName)) {
			// If requesting sharedInbox, we would not expect to be at a collection URL
			if (!empty($_GET['inbox']) && $_GET['inbox'] === 'shared ') {
				$this->sharedInboxRequest();
			}
			return;
		}

		if (
			$_SERVER['REQUEST_METHOD'] === 'GET' &&
			!$this->validateRequestMimeTypes('HTTP_ACCEPT', $this->mimeTypes())
		) {
			return;
		} else if (
			$_SERVER['REQUEST_METHOD'] === 'POST' &&
			!$this->validateRequestMimeTypes('HTTP_CONTENT_TYPE', $this->mimeTypes())
		) {
			return;
		}

		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' initialize()');
		}

		// This will compute to a method in this class. E.g., `$this->selfRequest()` or `$this->inboxRequest()`
		$do = ($_GET['request'] ?? 'self') . 'Request';

		if (!method_exists($this, $do)) {
			throw new Exception('Invalid ActivityPub request');
		}

		if ($this->system->debug) {
			error_log('DEBUG: Performing ActivityPub request "' . $do . '"');
		}

		$this->{$do}();
		$this->system->shutdown = true;
	}

	public function mimeTypes(): array {
		return [
			'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
			'application/activity+json',
			'application/ld+json',
			$this->system->debug ? 'text/html' : null,
		];
	}

	private function _activityPubServer() {
		return new ActivityPhp\Server([
			'cache' => [
				'enabled' => !$this->system->debug,
				'stream' => $this->system->dirStorage . '/cache/activitypub',
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

	public function followRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' followRequest()');
		}

		if (empty($_GET['remote'])) {
			throw new Exception('No remote account specified');
		}

		if (substr_count($_GET['remote'], '@') !== 1) {
			throw new Exception('Invalid remote account format (E1)');
		}

		// The reason to do this way is even if ActivityPub doesn't support paths in handles, I still want to in case it does somewhere
		// I even want it to support @example@localhost:1234/path
		// Ideally it will always be possible to test between localhost ports
		$exploded = explode('@', $_GET['remote']);

		if (!$this->ping(parse_url('//' . $exploded[1], PHP_URL_HOST))) { // Host without port
			throw new Exception('Could not ping remote host @ ' . $exploded[1] . ', giving up');
		}

		$server = $this->_activityPubServer();
		$actor = $server->actor($_GET['remote']);
		$sendToInbox = $actor->get('inbox') ?? null;

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL');
		}

		$collectionFolderName = $this->system->requests[Collection\Request::class]->collectionFolderName;

		// Create the JSON payload for the Follow activity (adjust as needed)
		$followActivity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $this->system->baseUri . '@' . $collectionFolderName . '#follow/' . md5(rand(0, 1000000) . microtime(true)),
			'type' => 'Follow',
			'actor' => $this->system->baseUri . '@' . $collectionFolderName,
			'object' => $actor->get('id'),
		];

		$activityJson = json_encode($followActivity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		// 201 response status means the follow request was "Created"
		$response = $server->inbox($_GET['remote'])->post(
			$this->createSignedRequest($sendToInbox, $activityJson)
		);

		header('Content-type: ' . $this->responseType);
		echo json_encode(['status' => $response->getStatusCode(), 'content' => $response->getContent()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	protected function ping(string $host) : bool {
		exec('ping -c 1 ' . escapeshellarg($host), $output, $resultCode);
		return $resultCode === 0;
	}

	public function followingRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' followingRequest()');
		}

		header('Content-type: ' . $this->responseType);
		echo json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function followersRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' followersRequest()');
		}

		header('Content-type: ' . $this->responseType);
		echo json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function inboxRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' inboxRequest()');
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new Exception('Expected POST request');
		}

		$requestBody = file_get_contents('php://input');
		$requestData = json_decode($requestBody);

		if (!$requestData) {
			throw new Exception('Could not load activity JSON');
		}

		if (empty($requestData->actor)) {
			throw new Exception('Could not determine request actor');
		}

		if (empty($requestData->type)) {
			throw new Exception('Could not determine request type');
		}

		if (empty($requestData->id)) {
			throw new Exception('Could not determine request ID');
		}

		if ($this->system->debug) {
			error_log('DEBUG: Received ' . $requestData->type . ' request from ' . $requestData->actor);
		}

		$collectionFolderName = $this->system->requests[Collection\Request::class]->collectionFolderName;

		/* BEGIN STORE INBOX ACTIVITY */

		$activityQueueFilename =
			$this->system->dirCollection . '/'
			. $collectionFolderName
			. '/.lipupini/inbox/'
			. date('Ymdhis')
			. '-' . microtime(true)
			. '-' . preg_replace('#[^\w]#', '', $requestData->type) . '.json';

		file_put_contents($activityQueueFilename, json_encode($requestData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		/* END STORE INBOX ACTIVITY */

		switch ($requestData->type) {
			case 'Follow' :
				$jsonData = [
					'@context' => ['https://www.w3.org/ns/activitystreams'],
					'id' => $this->system->baseUri . '@' . $collectionFolderName . '#accept/' . md5(rand(0, 1000000) . microtime(true)),
					'type' => 'Accept',
					'actor' => $this->system->baseUri . '@' . $collectionFolderName . '&request=outbox&page=1',
					'object' => $requestData->id,
				];
				break;
			case 'Undo' :
			case 'Accept' :
				http_response_code(201);
				return;
			default :
				throw new Exception('Unsupported ActivityPub type: ' . $requestData->type);
		}

		$activityJson = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$server = $this->_activityPubServer();
		$actor = $server->actor($requestData->actor);
		$sendToInbox = $actor->get('inbox') ?? null;

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL');
		}

		$response = $server->inbox($requestData->actor)->post(
			$this->createSignedRequest($sendToInbox, $activityJson)
		);

		//header('Content-type: ' . $this->responseType);
		//echo json_encode(["test"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function createSignedRequest(string $sendToInbox, string $activityJson) {
		$collectionFolderName = $this->system->requests[Collection\Request::class]->collectionFolderName;

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

	public function outboxRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' outboxRequest()');
		}

		$collectionFolderName = $this->system->requests[Collection\Request::class]->collectionFolderName;

		$jsonData = [
			'@context' => ['https://www.w3.org/ns/activitystreams'],
			'id' => $this->system->baseUri . '@' . $collectionFolderName . '&request=outbox',
			'type' => 'OrderedCollection',
			'first' => $this->system->baseUri . '@' . $collectionFolderName . '&request=outbox&page=1',
			'totalItems' => 1000,
		];

		header('Content-type: ' . $this->responseType);
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function sharedInboxRequest() {
		if ($this->system->debug) {
			error_log('DEBUG: ' . __CLASS__ . ' sharedInboxRequest()');
		}

		$requestData = print_r($_REQUEST, true) . "\n";
		$requestData .= print_r($_SERVER, true) . "\n";
		$requestData .= print_r(file_get_contents('php://input'), true);

		$activityQueueFilename =
			$this->system->dirStorage . '/sharedInbox/'
			. date('Ymdhis')
			. '-' . microtime(true)
			. '.json';

		file_put_contents($activityQueueFilename, $requestData);
	}

	public function selfRequest() {
		$collectionFolderName = $this->system->requests[Collection\Request::class]->collectionFolderName;

		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $this->system->baseUri . '@' . $collectionFolderName,
			'type' => 'Person',
			'following' => $this->system->baseUri . '@' . $collectionFolderName . '?request=following',
			'followers' => $this->system->baseUri . '@' . $collectionFolderName . '?request=followers',
			'inbox' => $this->system->baseUri . '@' . $collectionFolderName . '?request=inbox',
			'outbox' => $this->system->baseUri . '@' . $collectionFolderName . '?request=outbox',
			'preferredUsername' => $collectionFolderName,
			'name' => $collectionFolderName,
			'summary' => null,
			'url' => $this->system->baseUri . '@' . $collectionFolderName,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$this->system->baseUri . '@' . $collectionFolderName . '#main-key',
				'owner' => $this->system->baseUri . '@' . $collectionFolderName,
				'publicKeyPem' => file_get_contents($this->system->dirCollection . '/' . $collectionFolderName . '/.lipupini/.rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $this->system->baseUri . 'c/avatar/' . $collectionFolderName . '.png',
			],
			'endpoints' => [
				'sharedInbox' => $this->system->baseUri . '?inbox=shared',
			]
		];

		header('Content-type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function userAgent() {
		return  '(Lipupini/69.420; +' . $this->system->baseUri . ')';
	}
}
