<?php

namespace Module\Lipupini\Api;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public array $collectionData = [];

	use Collection\Trait\HasPaginatedCollectionData;

	public function initialize(): void {
		if (empty($this->system->request[Collection\Request::class]->name)) {
			return;
		}

		$collectionName = $this->system->request[Collection\Request::class]->name;

		if (!preg_match('#^/api/[^/]+/?(.*)$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)) {
			return;
		}

		$collectionPath = $matches[1] ?? '';
		$collectionPathExtension = pathinfo($collectionPath, PATHINFO_EXTENSION);

		if ($collectionPath) {
			if ($collectionPathExtension && $collectionPathExtension !== 'json') {
				throw new Exception('API request for media file must end in `.json');
			}
			// We only use the `.json` to ensure an API request. Remove `.json` to expose the collection filename
			$collectionPath = preg_replace('#\.json$#', '', $collectionPath);
		}

		$collectionUtility = new Collection\Utility($this->system);
		$collectionUtility->validateCollectionName($collectionName);

		$this->system->responseType = 'application/json';

		if (pathinfo($collectionPath, PATHINFO_EXTENSION)) {
			$this->system->responseContent = $this->renderCollectionFileJson($collectionName, $collectionPath);
		} else {
			$this->system->responseContent = $this->renderCollectionFolderJson($collectionName, $collectionPath, $collectionUtility);
		}

		$this->system->shutdown = true;
	}

	public function renderCollectionFolderJson(string $collectionName, string $collectionPath, Collection\Utility $collectionUtility) {
		$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($collectionName, $collectionPath);
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

			$this->collectionData[$filePath] += $this->getMediaInfo($mediaFileTypesByExtension, $collectionName, $filePath);
			$this->collectionData[$filePath]['item'] = $this->system->baseUri . 'api/' . $collectionName . '/' . $filePath . '.json';
		}

		return json_encode([
			'data' => $this->collectionData,
			'meta' => [
				'total' => $this->total,
				'perPage' => $this->system->itemsPerPage,
			],
		]);
	}

	public function getMediaInfo(array $mediaFileTypesByExtension, string $collectionName, string $filePath) {
		$extension = pathinfo($filePath, PATHINFO_EXTENSION);

		$return = [];

		$return['type'] = $mediaFileTypesByExtension[$extension]['mediaType'];
		$return['mime'] = $mediaFileTypesByExtension[$extension]['mimeType'];
		if ($return['type'] === 'image') {
			$return['url'] = $this->system->staticMediaBaseUri . $collectionName . '/image/large/' . $filePath;
			$return['thumbnail'] = $this->system->staticMediaBaseUri . $collectionName . '/image/thumbnail/' . $filePath;
		} else {
			$return['url'] =
				$this->system->staticMediaBaseUri . $collectionName . '/' . $return['type'] . '/' . $filePath;
			$return['thumbnail'] =
				$this->system->staticMediaBaseUri . $collectionName . '/thumbnail/' . $filePath . '.png';
		}

		return $return;
	}

	public function renderCollectionFileJson(string $collectionName, string $collectionFilePath) {
		if (!file_exists($this->system->dirCollection . '/' . $collectionName . '/' . $collectionFilePath)) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found']]);
		}

		$collectionDirectory = pathinfo($collectionFilePath, PATHINFO_DIRNAME);
		$collectionDirectory = $collectionDirectory === '.' ? '' : $collectionDirectory;

		$collectionUtility = new Collection\Utility($this->system);

		$this->collectionData = $collectionUtility->getCollectionData($collectionName, $collectionDirectory, true);

		if (
			!array_key_exists($collectionFilePath, $this->collectionData) ||
			($this->collectionData[$collectionFilePath]['visibility'] ?? null === 'hidden')
		) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found (in collection data)']]);
		}

		$this->collectionData[$collectionFilePath] +=
			$this->getMediaInfo($collectionUtility->mediaTypesByExtension(), $collectionName, $collectionFilePath);

		return json_encode(['collection' => $collectionName, 'filename' => $collectionFilePath] + $this->collectionData[$collectionFilePath]);
	}
}
