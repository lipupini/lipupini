<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Exception;
use Module\Lipupini\ActivityPub\RemoteActor;
use Module\Lipupini\ActivityPub\Request;
use Module\Lipupini\Request\Outgoing;

class Follow {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		if (empty($_GET['remote'])) {
			throw new Exception('No remote account specified', 400);
		}

		if (substr_count($_GET['remote'], '@') !== 1) {
			throw new Exception('Invalid remote account format (E1)', 400);
		}

		// The reason to do this way is even if ActivityPub doesn't support paths in handles, I still want to in case it does somewhere
		// I even want it to support @example@localhost:1234/path
		// Ideally it will always be possible to test between localhost ports
		$exploded = explode('@', $_GET['remote']);

		if (!Outgoing\Ping::host($exploded[1])) {
			throw new Exception('Could not ping remote host @ ' . $exploded[1] . ', giving up', 400);
		}

		$remoteActor = RemoteActor::fromHandle(
			handle: $_GET['remote'],
			cacheDir: $activityPubRequest->system->dirStorage . '/cache/ap'
		);

		$sendToInbox = $remoteActor->getInboxUrl();

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL', 400);
		}

		// Create the JSON payload for the Follow activity (adjust as needed)
		$followActivity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '#follow/' . md5(rand(0, 1000000) . microtime(true)),
			'type' => 'Follow',
			'actor' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
			'object' => $remoteActor->getId(),
		];

		$activityJson = json_encode($followActivity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$response = $activityPubRequest->sendSigned($sendToInbox, $activityJson);

		header('Content-type: ' . $activityPubRequest->mimeTypes()[0]);
		$activityPubRequest->system->responseContent = json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
