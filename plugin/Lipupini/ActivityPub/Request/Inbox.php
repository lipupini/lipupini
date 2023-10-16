<?php

namespace Plugin\Lipupini\ActivityPub\Request;

use Plugin\Lipupini\ActivityPub\Exception;
use Plugin\Lipupini\ActivityPub\Request;

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

		/* BEGIN STORE INBOX ACTIVITY */

		$activityQueueFilename =
			$activityPubRequest->system->dirCollection . '/'
			. $activityPubRequest->collectionFolderName
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
					'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '#accept/' . md5(rand(0, 1000000) . microtime(true)),
					'type' => 'Accept',
					'actor' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '&request=outbox&page=1',
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

		$server = $activityPubRequest->activityPubServer();
		$actor = $server->actor($requestData->actor);
		$sendToInbox = $actor->get('inbox') ?? null;

		if (!filter_var($sendToInbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL');
		}

		$response = $server->inbox($requestData->actor)->post(
			$activityPubRequest->createSignedRequest($sendToInbox, $activityJson)
		);

		header('Content-type: ' . $activityPubRequest->responseType);
		// Just pass through the status code received from the remote
		http_response_code($response->getStatusCode());
	}
}
