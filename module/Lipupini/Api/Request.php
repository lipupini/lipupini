<?php

namespace Module\Lipupini\Api;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Queued;

class Request extends Queued {
	public array $collectionData = [];

	use Collection\Trait\HasPaginatedCollectionData;
	use Collection\Trait\CollectionRequest;

	public function initialize(): void {
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . 'api/?#', $_SERVER['REQUEST_URI_DECODED'])) return;

		$collectionUtility = new Collection\Utility($this->system);

		if (rtrim(parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH), '/') === '/api') {
			$this->system->responseType = 'application/json';
			$this->system->responseContent = json_encode($collectionUtility->allCollectionFolders());
			return;
		}

		$this->collectionNameFromSegment(2);

		$this->system->shutdown = true;

		$collectionApiPath = preg_replace(
			'#^/api/' . preg_quote($this->collectionName) . '/?#', '',
			parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH)
		);

		if (!pathinfo($collectionApiPath, PATHINFO_EXTENSION)) {
			$this->system->responseType = 'application/json';
			$this->system->responseContent = $this->renderCollectionFolderJson($collectionApiPath, $collectionUtility);
			return;
		}

		if (pathinfo($_SERVER['REQUEST_URI_DECODED'], PATHINFO_EXTENSION) !== 'json') {
			throw new Exception('API request for media file must end in `.json');
		};

		$collectionFilePath = rawurldecode(preg_replace('#\.json$#', '', $collectionApiPath));

		// Make sure file in collection exists before proceeding
		if (
			!file_exists($this->system->dirCollection . '/' . $this->collectionName . '/' . $collectionFilePath)
		) return;

		$this->system->responseType = 'application/json';
		$this->system->responseContent = $this->renderCollectionFileJson($collectionFilePath);
	}

	public function renderCollectionFolderJson(string $collectionPath, Collection\Utility $collectionUtility) {
		$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($this->collectionName, $collectionPath);
		$this->loadPaginationAttributes();
		$mediaFileTypesByExtension = $collectionUtility->mediaTypesByExtension();

		foreach ($this->collectionData as $filePath => $data) {
			$extension = pathinfo($filePath, PATHINFO_EXTENSION);
			if (!$extension) {
				$this->collectionData[$filePath]['type'] = 'folder';
				continue;
			} else if (!in_array($extension, array_keys($mediaFileTypesByExtension))) {
				continue;
			}

			$urlEncodedFilePath = implode('/', array_map('rawurlencode', explode('/', $filePath)));

			$this->collectionData[$filePath] += $this->getMediaInfo($collectionUtility, $mediaFileTypesByExtension, $filePath);
			$this->collectionData[$filePath]['item'] = $this->system->baseUri . 'api/' . $this->collectionName . '/' . $urlEncodedFilePath . '.json';
		}

		return json_encode([
			'data' => $this->collectionData,
			'meta' => [
				'total' => $this->total,
				'perPage' => $this->system->itemsPerPage,
			],
		]);
	}

	public function getMediaInfo(Collection\Utility $collectionUtility, array $mediaFileTypesByExtension, string $filePath) {
		$extension = pathinfo($filePath, PATHINFO_EXTENSION);
		$filePath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
		$return = [];
		$return['type'] = $mediaFileTypesByExtension[$extension]['mediaType'];
		$return['mime'] = $mediaFileTypesByExtension[$extension]['mimeType'];
		switch ($return['type']) {
			case 'image' :
				foreach (array_keys($this->system->mediaSize) as $mediaSize) {
					$return[$mediaSize] = $collectionUtility->assetUrl($this->collectionName, $return['type'] . '/' . $mediaSize, $filePath);
				}
				break;

			case 'audio' :
				$return['url'] = $collectionUtility->assetUrl($this->collectionName, $return['type'], $filePath);
				$return['thumbnail'] = $collectionUtility->thumbnailUrl($this->collectionName, $return['type'] . '/thumbnail', $filePath);
				$return['waveform'] = $collectionUtility->waveformUrl($this->collectionName, $return['type'] . '/waveform', $filePath);
				break;

			case 'video' :
				$return['url'] = $collectionUtility->assetUrl($this->collectionName, $return['type'], $filePath);
				$return['thumbnail'] = $collectionUtility->thumbnailUrl($this->collectionName, $return['type'] . '/thumbnail', $filePath);
				break;
		}

		return $return;
	}

	public function renderCollectionFileJson(string $collectionFilePath) {
		if (!file_exists($this->system->dirCollection . '/' . $this->collectionName . '/' . $collectionFilePath)) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found']]);
		}

		$collectionDirectory = pathinfo($collectionFilePath, PATHINFO_DIRNAME);
		$collectionDirectory = $collectionDirectory === '.' ? '' : $collectionDirectory;

		$collectionUtility = new Collection\Utility($this->system);

		$this->collectionData = $collectionUtility->getCollectionData($this->collectionName, $collectionDirectory, true);

		if (
			!array_key_exists($collectionFilePath, $this->collectionData) ||
			($this->collectionData[$collectionFilePath]['visibility'] ?? null === 'hidden')
		) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found (in collection data)']]);
		}

		$this->collectionData[$collectionFilePath] +=
			$this->getMediaInfo($collectionUtility, $collectionUtility->mediaTypesByExtension(), $collectionFilePath);

		return json_encode(['collection' => $this->collectionName, 'filename' => $collectionFilePath] + $this->collectionData[$collectionFilePath]);
	}
}
