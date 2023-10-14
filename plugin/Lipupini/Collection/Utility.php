<?php

namespace Plugin\Lipupini\Collection;

use System\State;
use Plugin\Lipupini\Collection;

class Utility {
	public function __construct(private State $system) { }

	public function validateCollectionFolderName(string $collectionFolderName): void {
		if (!is_dir($this->system->dirCollection . '/' . $collectionFolderName)) {
			throw new Exception('Could not find collection from identifier');
		}
	}

	public function getCollectionData(string $collectionFolderName, string $collectionRequestPath) {
		$collectionRootPath = $this->system->dirCollection . '/' . $collectionFolderName;

		// `$system->collectionRequestPath` could be `memes/cats`, which would be relative to `$collectionRootPath`
		if ($collectionRequestPath) {
			if (pathinfo($collectionRequestPath, PATHINFO_EXTENSION)) {
				// `$system->collectionRequestPath` could be a file: `memes/cats/cat123.jpg`
				$collectionRelativePath = dirname($collectionRequestPath) === '.' ? '' : dirname($collectionRequestPath);
			} else {
				// `$system->collectionRequestPath` could be a directory: `memes/cats`
				$collectionRelativePath = $collectionRequestPath;
			}
		} else {
			// This would be the root of the collection
			$collectionRelativePath = '';
		}

		$collectionAbsolutePath = $collectionRelativePath ? $collectionRootPath . '/' . $collectionRelativePath : $collectionRootPath;
		$return = $collectionData = [];
		$filesJsonPath = $collectionAbsolutePath . '/.lipupini/.files.json';
		if (file_exists($filesJsonPath)) {
			$collectionData = json_decode(file_get_contents($filesJsonPath), true);
			// Process collection data first, since it can determine the display order
			foreach ($collectionData as $filename => $fileData) {
				if ($collectionRelativePath) {
					$filename = $collectionRelativePath . '/' . $filename;
				}
				if (!file_exists($collectionRootPath . '/' . $filename)) {
					throw new Exception('Could not find file for entry in ' . $collectionFolderName . '/.lipupini/.files.json');
				}
				$return[$filename] = $fileData;
			}
		}
		foreach (new \DirectoryIterator($collectionAbsolutePath) as $fileData) {
			if ($fileData->isDot() || $fileData->getFilename()[0] === '.') {
				continue;
			}
			$filePath = $collectionRelativePath ? $collectionRelativePath . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			// Get data from `$collectionData` if exists
			$return[$filePath] = array_key_exists($fileData->getFilename(), $collectionData) ? $collectionData[$fileData->getFilename()] : [];
		}
		return $return;
	}

	public function getSearchData($query) {
		$collectionFolderName = $this->system->requests[Collection\FolderRequest::class]->collectionFolderName;

		if (!$query) {
			throw new Exception('No query specified');
		}

		$collectionRootPath = $this->system->dirCollection . '/' . $collectionFolderName;

		$searchesJsonPath = $collectionRootPath . '/.lipupini/.savedSearches.json';
		if (!file_exists($searchesJsonPath)) {
			throw new Exception('Could not find search data');
		}
		$searchData = json_decode(file_get_contents($searchesJsonPath), true);
		if (!array_key_exists($query, $searchData)) {
			throw new Exception('Could not find specified search');
		}
		return $searchData[$query];
	}
}
