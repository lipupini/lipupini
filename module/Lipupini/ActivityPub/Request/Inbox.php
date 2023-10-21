<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Exception;
use Module\Lipupini\ActivityPub\RemoteActor;
use Module\Lipupini\ActivityPub\Request;
use Module\Lipupini\Request\Incoming;

class Inbox {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
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

		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: Received ' . $requestData->type . ' request from ' . $requestData->actor);
		}

		if (empty($_SERVER['HTTP_SIGNATURE'])) {
			throw new Exception('Expected request to be signed');
		}

		$remoteActor = RemoteActor::fromUrl(
			url: $requestData->actor,
			cacheDir: $activityPubRequest->system->dirStorage . '/cache/ap'
		);

		if (!(new Incoming\Signature)->verify(
			$remoteActor->getPublicKeyPem(),
			$_SERVER,
			parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), // Path without query string
			$requestBody
		)) {
			throw new Exception('HTTP Signature did not validate');
		}

		/* BEGIN STORE INBOX ACTIVITY */

		$inboxFolder = $activityPubRequest->system->dirCollection . '/'
			. $activityPubRequest->collectionFolderName
			. '/.lipupini/inbox/';

		if (!is_dir($inboxFolder)) {
			mkdir($inboxFolder, 0755, true);
		}

		$activityQueueFilename = $inboxFolder
			. date('Ymdhis')
			. '-' . microtime(true)
			. '-' . preg_replace('#[^\w]#', '', $requestData->type) . '.json';

		file_put_contents($activityQueueFilename, json_encode($requestData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		/* END STORE INBOX ACTIVITY */

		switch ($requestData->type) {
			case 'Follow' :
				$jsonData = [
					'@context' => 'https://www.w3.org/ns/activitystreams',
					'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '#accept/' . md5(rand(0, 1000000) . microtime(true)),
					'type' => 'Accept',
					'actor' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
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

		$response = $activityPubRequest->sendSigned($remoteActor->getInboxUrl(), $activityJson);

		header('Content-type: ' . $activityPubRequest->mimeTypes()[0]);
		// Just pass through the status code received from the remote
		http_response_code($response['code']);
		$activityPubRequest->system->responseContent = json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
