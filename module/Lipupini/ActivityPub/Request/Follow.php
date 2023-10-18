<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Exception;
use Module\Lipupini\ActivityPub\Request;

class Follow {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
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

		if (!$activityPubRequest->ping(parse_url('//' . $exploded[1], PHP_URL_HOST))) { // Host without port
			throw new Exception('Could not ping remote host @ ' . $exploded[1] . ', giving up');
		}

		$server = $activityPubRequest->activityPubServer();
		$actor = $server->actor($_GET['remote']);
		$sendToInbox = $actor->get('inbox') ?? null;

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL');
		}

		// Create the JSON payload for the Follow activity (adjust as needed)
		$followActivity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '#follow/' . md5(rand(0, 1000000) . microtime(true)),
			'type' => 'Follow',
			'actor' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
			'object' => $actor->get('id'),
		];

		$activityJson = json_encode($followActivity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		// 201 response status means the follow request was "Created"
		$response = $server->inbox($_GET['remote'])->post(
			$activityPubRequest->createSignedRequest($sendToInbox, $activityJson)
		);

		$activityPubRequest->system->responseContent = json_encode([
			'status' => $response->getStatusCode(),
			'content' => $response->getContent()
		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
