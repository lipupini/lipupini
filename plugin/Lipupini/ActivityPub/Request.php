<?php

namespace Plugin\Lipupini\ActivityPub;

use ActivityPhp;
use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public string $activityPubAccount = '';
	public string $responseType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	public function initialize(): void {
		if (empty($this->system->requests[Collection\Request::class]->collectionFolderName)) {
			// If requesting sharedInbox, we would not expect to be at a collection URL
			if (!empty($_GET['inbox']) && $_GET['inbox'] === 'shared') {
				$this->sharedInboxRequest();
			}
			return;
		}

		$this->activityPubAccount = $this->system->requests[Collection\Request::class]->collectionFolderName;

		if (!$this->clientAcceptsMimeTypes($this->mimeTypes())) {
			return;
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
		$sendToInbox = $this->getInboxUrl($actor);

		// Create the JSON payload for the Follow activity (adjust as needed)
		$followActivity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $this->system->baseUri . '@' . $this->activityPubAccount . '#follow/' . md5(rand(0, 1000000) . microtime(true)),
			'type' => 'Follow',
			'actor' => $this->system->baseUri . '@' . $this->activityPubAccount,
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

	protected function getInboxUrl($actor) {
		$actorInboxUrl = $actor->get('inbox') ?? null;
		$sharedInboxUrl = $actor->get('endpoints')['sharedInbox'] ?? null;
		// This would include the port # in the host, if any
		$inboxUrl = $sharedInboxUrl ?? $actorInboxUrl ?? null;
		if (!filter_var($inboxUrl, FILTER_VALIDATE_URL)) {
			throw new Exception('Inbox URL does not seem right');
		}
		return $inboxUrl;
	}

	protected function ping(string $host) : bool {
		exec('ping -c 1 ' . escapeshellarg($host), $output, $resultCode);
		return $resultCode === 0;
	}

	public function followingRequest() {
		header('Content-type: ' . $this->responseType);
		echo json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function followersRequest() {
		header('Content-type: ' . $this->responseType);
		echo json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function inboxRequest() {
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

		switch ($requestData->type) {
			case 'Follow' :
				$jsonData = [
					'@context' => ['https://www.w3.org/ns/activitystreams'],
					'id' => $this->system->baseUri . '@' . $this->activityPubAccount . '#accept/' . md5(rand(0, 1000000) . microtime(true)),
					'type' => 'Accept',
					'actor' => $this->system->baseUri . '@' . $this->activityPubAccount . '&request=outbox&page=1',
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
		$sendToInbox = $this->getInboxUrl($actor);

		$response = $server->inbox($_GET['remote'])->post(
			$this->createSignedRequest($sendToInbox, $activityJson)
		);

		var_dump($response);

		//header('Content-type: ' . $this->responseType);
		//echo json_encode(["test"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function createSignedRequest(string $sendToInbox, string $activityJson) {
		return Lipupini\Http\Signature::signedRequest(
			privateKeyPath: $this->system->dirCollection . '/' . $this->activityPubAccount . '/.lipupini/.rsakey.private',
			keyId: $this->system->baseUri . '@' . $this->activityPubAccount . '#main-key',
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
		$jsonData = [
			'@context' => ['https://www.w3.org/ns/activitystreams'],
			'id' => $this->system->baseUri . '@' . $this->activityPubAccount . '&request=outbox',
			'type' => 'OrderedCollection',
			'first' => $this->system->baseUri . '@' . $this->activityPubAccount . '&request=outbox&page=1',
			'totalItems' => 1000,
		];

		header('Content-type: ' . $this->responseType);
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function sharedInboxRequest() {
		error_log('begin shared inbox request');
		error_log(print_r($_REQUEST, true));
		error_log(print_r($_SERVER, true));
		error_log(print_r(file_get_contents('php://input'), true));

		$this->inboxRequest();
	}

	public function selfRequest() {
		$jsonData = [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams', [
					'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
				],
			],
			'id' => $this->system->baseUri . '@' . $this->activityPubAccount,
			'type' => 'Person',
			'following' => $this->system->baseUri . '@' . $this->activityPubAccount . '?request=following',
			'followers' => $this->system->baseUri . '@' . $this->activityPubAccount . '?request=followers',
			'inbox' => $this->system->baseUri . '@' . $this->activityPubAccount . '?request=inbox',
			'outbox' => $this->system->baseUri . '@' . $this->activityPubAccount . '?request=outbox',
			'preferredUsername' => $this->activityPubAccount,
			'name' => $this->activityPubAccount,
			'summary' => null,
			'url' => $this->system->baseUri . '@' . $this->activityPubAccount,
			'manuallyApprovesFollowers' => false,
			'publicKey' => [
				'id' =>$this->system->baseUri . '@' . $this->activityPubAccount . '#main-key',
				'owner' => $this->system->baseUri . '@' . $this->activityPubAccount,
				'publicKeyPem' => file_get_contents($this->system->dirCollection . '/' . $this->activityPubAccount . '/.lipupini/.rsakey.public')
			],
			'icon' => [
				'type' => 'Image',
				'mediaType' => 'image/png',
				'url' => $this->system->baseUri . 'c/avatar/' . $this->activityPubAccount . '.png',
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
