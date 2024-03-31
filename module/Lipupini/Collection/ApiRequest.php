<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class ApiRequest extends Http {
	public array $collectionData = [];

	use Collection\Trait\HasPaginatedCollectionData;

	public function initialize(): void {
		if (!preg_match('#^/api/([^/]+)/?(.*)$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)) {
			return;
		}

		// This should never happen, though not sure if it's only the browser that will prevent it
		if (str_contains($_SERVER['REQUEST_URI'], '..')) {
			throw new Exception('Suspicious collection URL');
		}

		$collectionFolderName = $matches[1];
		$collectionPath = $matches[2] ?? '';
		$collectionPathExtension = pathinfo($collectionPath, PATHINFO_EXTENSION);

		if ($collectionPath) {
			if ($collectionPathExtension && $collectionPathExtension !== 'json') {
				throw new Exception('API request for media file must end in `.json');
			}
			// We only use the `.json` to ensure an API request. Remove `.json` to expose the collection filename
			$collectionPath = preg_replace('#\.json$#', '', $collectionPath);
		}

		$collectionUtility = new Collection\Utility($this->system);
		$collectionUtility->validateCollectionFolderName($collectionFolderName);

		$this->system->responseType = 'application/json';

		if (pathinfo($collectionPath, PATHINFO_EXTENSION)) {
			$this->system->responseContent = $this->renderCollectionFileJson($collectionFolderName, $collectionPath);
		} else {
			$this->system->responseContent = $this->renderCollectionFolderJson($collectionFolderName, $collectionPath, $collectionUtility);
		}

		$this->system->shutdown = true;
	}

	public function renderCollectionFolderJson(string $collectionFolderName, string $collectionPath, Utility $collectionUtility) {
		$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($collectionFolderName, $collectionPath);
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

			$this->collectionData[$filePath] += $this->getMediaInfo($mediaFileTypesByExtension, $collectionFolderName, $filePath);
		}

		return json_encode([
			'data' => $this->collectionData,
			'meta' => [
				'total' => $this->total,
				'perPage' => $this->system->itemsPerPage,
			],
		]);
	}

	public function getMediaInfo(array $mediaFileTypesByExtension, string $collectionFolderName, string $filePath) {
		$extension = pathinfo($filePath, PATHINFO_EXTENSION);

		$return = [];

		$return['type'] = $mediaFileTypesByExtension[$extension]['mediaType'];
		$return['mime'] = $mediaFileTypesByExtension[$extension]['mimeType'];
		if ($return['type'] === 'image') {
			$return['url'] = $this->system->staticMediaBaseUri . $collectionFolderName . '/image/large/' . $filePath;
			$return['thumbnail'] = $this->system->staticMediaBaseUri . $collectionFolderName . '/image/thumbnail/' . $filePath;
		} else {
			$return['url'] =
				$this->system->staticMediaBaseUri . $collectionFolderName . '/' . $return['type'] . '/' . $filePath;
			$return['thumbnail'] =
				$this->system->staticMediaBaseUri . $collectionFolderName . '/thumbnail/' . $filePath . '.png';
		}

		return $return;
	}

	public function renderCollectionFileJson(string $collectionFolderName, string $collectionFilePath) {
		if (!file_exists($this->system->dirCollection . '/' . $collectionFolderName . '/' . $collectionFilePath)) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found']]);
		}

		$collectionDirectory = pathinfo($collectionFilePath, PATHINFO_DIRNAME);
		$collectionDirectory = $collectionDirectory === '.' ? '' : $collectionDirectory;

		$collectionUtility = new Collection\Utility($this->system);

		$this->collectionData = $collectionUtility->getCollectionData($collectionFolderName, $collectionDirectory);

		if (!array_key_exists($collectionFilePath, $this->collectionData)) {
			http_response_code(404);
			return json_encode(['error' => ['code' => 404, 'message' => 'File not found (in collection data)']]);
		}

		$this->collectionData[$collectionFilePath] +=
			$this->getMediaInfo($collectionUtility->mediaTypesByExtension(), $collectionFolderName, $collectionFilePath);

		return json_encode(['collection' => $collectionFolderName, 'filename' => $collectionFilePath] + $this->collectionData[$collectionFilePath]);
	}
}
