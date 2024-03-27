<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;
use Module\Lipupini\Collection;
use Module\Lipupini\Rss\Exception;

class Outbox extends Request {
	public array $collectionData = [];
	public int $perPage = 48;

	use Collection\Trait\HasPaginatedCollectionData;

	public function initialize(): void {
		if ($this->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$collectionFolderName = $this->system->requests[Collection\Request::class]->folderName;

		$this->collectionData = (new Collection\Utility($this->system))->getCollectionDataRecursive($collectionFolderName);

		if (empty($_GET['page'])) {
			$jsonData = [
				'@context' => 'https://www.w3.org/ns/activitystreams',
				'id' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox',
				'type' => 'OrderedCollection',
				'first' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox&page=1',
				'totalItems' => count($this->collectionData),
			];
			$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
			return;
		}

		$this->loadPaginationAttributes();

		$items = [];
		foreach ($this->collectionData as $filePath => $metaData) {

			$htmlUrl = $this->system->baseUri . '@' . $collectionFolderName . '/' . $filePath . '.html';
			if (empty($metaData['date'])) {
				$metaData['date'] = (new \DateTime)
					->setTimestamp(filemtime($this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath))
					->format(\DateTime::ISO8601);
			} else {
				$metaData['date'] = (new \DateTime($metaData['date']))
					->format(\DateTime::ISO8601);
			}

			$item = [
				'@context' => [
					'https://www.w3.org/ns/activitystreams',
					'https://w3id.org/security/v1', [
						'sensitive' => 'as:sensitive',
					],
				],
				'id' => $htmlUrl . '#activity',
				'actor' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=profile',
				'published' => $metaData['date'],
				'type' => 'Create',
				'to' => [
					'https://www.w3.org/ns/activitystreams#Public'
				],
				'cc' => [
					$this->system->baseUri . '@' . $collectionFolderName .'?ap=followers'
				]
			];

			$object = [
				'id' => $htmlUrl,
				'published' => $metaData['date'],
				'url' => $htmlUrl,
				'mediaType' => 'text/html',
				'inReplyTo' => null,
				'summary' => $filePath,
				'type' => 'Page',
				'name' => $filePath,
				'attributedTo' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=profile',
				'sensitive' => $metaData['sensitive'] ?? false,
				'content' => $metaData['caption'] ?? $filePath,
				'contentMap' => [
					'en' => $metaData['caption'] ?? $filePath,
				],
			];

			$extension = pathinfo($filePath, PATHINFO_EXTENSION);

			if (in_array($extension, array_keys($this->system->mediaType['image']))) {
				$object['attachment'] = [
					'type' => 'Image',
					'mediaType' => $this->system->mediaType['image'][$extension],
					'url' => $this->system->staticMediaBaseUri . $collectionFolderName . '/image/large/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys($this->system->mediaType['video']))) {
				$object['attachment'] = [
					'type' => 'Video',
					'mediaType' => $this->system->mediaType['video'][$extension],
					'url' => $this->system->staticMediaBaseUri . $collectionFolderName . '/video/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys($this->system->mediaType['audio']))) {
				$object['attachment'] = [
					'type' => 'Audio',
					'mediaType' => $this->system->mediaType['audio'][$extension],
					'url' => $this->system->staticMediaBaseUri . $collectionFolderName . '/audio/' . $filePath,
					'name' => $filePath,
				];
			} else if (in_array($extension, array_keys($this->system->mediaType['text']))) {
				$object['attachment'] = [
					'type' => 'Note',
					'mediaType' => 'text/html',
					'url' => $this->system->staticMediaBaseUri . $collectionFolderName . '/text/' . $filePath . '.html',
					'name' => $filePath,
				];
			} else {
				throw new Exception('Unexpected file extension: ' . $extension, 400);
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
			'id' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox&page=' . (int)$_GET['page'],
			'type' => 'OrderedCollectionPage',
			'partOf' => $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox',
			'totalItems' => count($this->collectionData),
			'orderedItems' => $items
		];

		if ($this->page > 1) {
			$outboxJsonArray['prev'] = $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox&page=' . ($this->page - 1);
		}

		if ($this->page < $this->numPages) {
			$outboxJsonArray['next'] = $this->system->baseUri . '@' . $collectionFolderName . '?ap=outbox&page=' . ($this->page + 1);
		}

		$this->system->responseContent = json_encode($outboxJsonArray, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
