<?php

namespace Plugin\Lipupini\ActivityPub;

use ActivityPhp;
use phpseclib3\Crypt\PublicKeyLoader;
use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public string $activityPubAccount = '';
	public string $responseType = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

	public function initialize() {
		if (empty($this->system->requests[Collection\Request::class]->collectionFolderName)) {
			// If requesting sharedInbox, we would not expect to be at a collection URL
			if (isset($_GET['sharedInbox'])) {
				$this->sharedInboxRequest();
			}
			return;
		}

		$this->activityPubAccount = $this->system->requests[Collection\Request::class]->collectionFolderName;

		if (!$this->clientAcceptsMimeTypes($this->mimeTypes())) {
			return;
		}

		$do = ($_GET['request'] ?? 'self') . 'Request';

		if (!method_exists($this, $do)) {
			throw new Exception('Invalid ActivityPub request');
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
		$parsedHost = $tmp = parse_url('//' . $exploded[1]);

		exec('ping -c 1 ' . escapeshellarg($parsedHost['host']), $output, $resultCode);

		if ($resultCode !== 0) {
			throw new Exception('Could not ping remote host @ ' . $parsedHost['host'] . ', giving up');
		}

		$server = $this->_activityPubServer();
		$actor = $server->actor($_GET['remote']);

		$inboxUrl = $actor->get('inbox') ?? null;
		$sharedInboxUrl = $actor->get('endpoints')['sharedInbox'] ?? null;
		// This would include the port # in the host, if any
		$sendToInbox = $sharedInboxUrl ?? $inboxUrl ?? null;

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Inbox URL does not seem right');
		}

		// Create the JSON payload for the Follow activity (adjust as needed)
		$followActivity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $this->system->baseUri . '@' . $this->activityPubAccount . '#follow/' . md5(rand(0, 1000000) . microtime(true)),
			'type' => 'Follow',
			'actor' => $this->system->baseUri . '@' . $this->activityPubAccount,
			'object' => $actor->get('id'),
		];

		$activityJson = json_encode($followActivity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$remotePublicKeyId = $actor->get('publicKey')['id'];
		$request = $this->createSignedRequest($sendToInbox, $remotePublicKeyId, $activityJson);

		$response = $server->inbox($_GET['remote'])->post($request);

		var_dump($response->getStatusCode(), $response->getContent());
		exit();

		/*$curlHeaders = $request->headers->all();
		$curlHeaders = array_map(function($k, $v){
			return "$k: $v[0]";
		}, array_keys($curlHeaders), $curlHeaders);

		var_dump($sendToInbox);

		$ch = curl_init($sendToInbox);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $activityJson);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		var_dump($response);*/


		//$webFinger = $actor->webfinger();


		//$inbox = $server->inbox($_GET['remote'])->post();
		//$outbox = $server->outbox($_GET['remote']);

		//var_dump($outbox, $webFinger);
		/*// Prepare a stack
		$pages = [];

		// Browse first page
		$page = $outbox->getPage($outbox->get()->first);

		// Browse all pages and get public actvities
		$pages[] = $page;
		while ($page->next !== null) {
			$page = $outbox->getPage($page->next);
			$pages[] = $page;
		}*/

		header('Content-type: ' . $this->responseType);
		echo json_encode($inboxUrl, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	protected function createSignedRequest(string $toUrl, string $remotePublicKeyId, string $body) {
		$rsa = PublicKeyLoader::loadPrivateKey(
			file_get_contents(
				$this->system->dirCollection . '/' . $this->activityPubAccount . '/.lipupini/.rsakey.private'
			)
		)->withHash('sha256'); // private key

		$parsedToUrl = parse_url($toUrl);
		$date = gmdate('D, d M Y H:i:s T', time());
		$pathWithQuery = $parsedToUrl['path'] . (!empty($parsedToUrl['query']) ? '?' . $parsedToUrl['query'] : '');
		// `$parsedToUrl['host']` would not include the port number, if any
		$plaintext = '(request-target) post ' . $pathWithQuery . "\n" . 'host: ' . $parsedToUrl['host'] . "\n" . 'date: ' . $date;
		$signature = $rsa->sign($plaintext);

		$request = \Symfony\Component\HttpFoundation\Request::create(
			$toUrl,
			'POST',
			[], // parameters
			[], // cookies
			[], // files
			[], // $_SERVER,
			$body
		);

		//$localPublicKeyId = $this->system->baseUri . '@' . $this->activityPubAccount . '#main-key';

		$request->headers->set('Accept', $this->mimeTypes()[0]);
		$request->headers->set('Signature', 'keyId="' . $remotePublicKeyId . '",algorithm="rsa-sha256",headers="(request-target) host date",signature="' . base64_encode($signature) . '"');
		$request->headers->set('User-Agent', $this->userAgent());
		$request->headers->set('Host', $parsedToUrl['host']); // This would not include the port number, if any
		$request->headers->set('Date', $date);
		$request->headers->set('Digest', 'SHA-256=' . base64_encode(hash('sha256', $body, true)));

		return $request;
	}

	public function followingRequest() {

	}

	public function followersRequest() {

	}

	public function inboxRequest() {
		header('Content-type: ' . $this->responseType);
		echo json_encode(["test"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function outboxRequest() {
		$jsonData = [
			'@context' => [
				'https://www.w3.org/ns/activitystreams'
			],
			'id' => $this->system->baseUri . '@' . $this->activityPubAccount . '&request=outbox',
			'type' => 'OrderedCollection',
			'first' => $this->system->baseUri . '@' . $this->activityPubAccount . '&request=outbox&page=1',
			'totalItems' => 1000,
		];

		header('Content-type: ' . $this->responseType);
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function sharedInboxRequest() {
		$this->system->shutdown = true;
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
				'sharedInbox' => $this->system->baseUri . '?sharedInbox',
			]
		];

		header('Content-type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function userAgent() {
		return  '(Lipupini/69.420; +' . $this->system->baseUri . ')';
	}
}
