<?php

namespace Plugin\Lipupini\ActivityPub\Request;

use Plugin\Lipupini\ActivityPub\Request;
use Plugin\Lipupini\Collection;
use Plugin\Lipupini\Rss\Exception;

class Outbox {
	public array $collectionData = [];
	public int $perPage = 20;

	use Collection\Trait\HasPaginatedCollectionData;

	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$this->collectionData = (new Collection\Utility($activityPubRequest->system))
			->getCollectionDataRecursive($activityPubRequest->collectionFolderName);

		if (empty($_GET['page'])) {
			$jsonData = [
				'@context' => ['https://www.w3.org/ns/activitystreams'],
				'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=outbox',
				'type' => 'OrderedCollection',
				'first' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=outbox&page=1',
				'totalItems' => count($this->collectionData),
			];
			$activityPubRequest->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
			return;
		}

		$this->loadPaginationAttributes();

		$items = [];
		foreach ($this->collectionData as $filePath => $metaData) {

			$htmlUrl = $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '/' . $filePath . '.html';
			if (empty($metaData['date'])) {
				$metaData['date'] = (new \DateTime)
					->setTimestamp(filemtime($activityPubRequest->system->dirCollection . '/' . $activityPubRequest->collectionFolderName . '/' . $filePath))
					->format(\DateTime::ISO8601);
			} else {
				$metaData['date'] = (new \DateTime($metaData['date']))
					->format(\DateTime::ISO8601);
			}

			$item = [
				'id' => $htmlUrl . '#activity',
				'actor' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
				'published' => $metaData['date'],
				'type' => 'Create',
				'to' => 'https://www.w3.org/ns/activitystreams#Public',
				'cc' => [
					$activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName .'?request=followers'
				]
			];

			$object = [
				'id' => $htmlUrl,
				'published' => $metaData['date'],
				'url' => $htmlUrl,
				'inReplyTo' => null,
				'summary' => $filePath,
				'type' => 'Note',
				'attributedTo' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName,
				'sensitive' => $metaData['sensitive'] ?? false,
				'contentMap' => [
					'en' => $metaData['caption'] ?? $filePath
				],
			];

			$extension = pathinfo($filePath, PATHINFO_EXTENSION);

			if (in_array($extension, array_keys(Collection\MediaProcessor\ImageRequest::mimeTypes()))) {
				$object['attachment'] = [
					'type' => 'Image',
					'mediaType' => Collection\MediaProcessor\ImageRequest::mimeTypes()[$extension],
					'url' => $activityPubRequest->system->cacheBaseUri . 'file/' . $activityPubRequest->collectionFolderName . 'image/large/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\VideoRequest::mimeTypes()))) {
				$object['attachment'] = [
					'type' => 'Video',
					'mediaType' => Collection\MediaProcessor\VideoRequest::mimeTypes()[$extension],
					'url' => $activityPubRequest->system->cacheBaseUri . 'file/' . $activityPubRequest->collectionFolderName . 'video/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\AudioRequest::mimeTypes()))) {
				$object['attachment'] = [
					'type' => 'Audio',
					'mediaType' => Collection\MediaProcessor\AudioRequest::mimeTypes()[$extension],
					'url' => $activityPubRequest->system->cacheBaseUri . 'file/' . $activityPubRequest->collectionFolderName . 'audio/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\MarkdownRequest::mimeTypes()))) {
				$object['attachment'] = [
					'type' => 'Note',
					'mediaType' => 'text/html',
					'url' => $activityPubRequest->system->cacheBaseUri . 'file/' . $activityPubRequest->collectionFolderName . 'markdown/' . $filePath . '.html',
					'name' => $filePath,
				];
			} else {
				throw new Exception('Unexpected file extension: ' . $extension);
			}

			$item['object'] = $object;
			$items[] = $item;
		}

		$outboxJsonArray = [
			'@context' => [
				'https://www.w3.org/ns/activitystreams', [
					'sensitive' => 'as:sensitive',
				],
			],
			'id' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=outbox&page=' . (int)$_GET['page'],
			'type' => 'OrderedCollectionPage',
			'partOf' => $activityPubRequest->system->baseUri . '@' . $activityPubRequest->collectionFolderName . '?request=outbox',
			'totalItems' => count($this->collectionData),
			'orderedItems' => $items
		];

		$activityPubRequest->system->responseContent = json_encode($outboxJsonArray, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
