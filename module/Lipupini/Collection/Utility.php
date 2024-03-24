<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;

class Utility {
	public function __construct(private State $system) { }

	public function validateCollectionFolderName(string $collectionFolderName): void {
		if (!is_dir($this->system->dirCollection . '/' . $collectionFolderName)) {
			throw new Exception('Could not find collection from identifier');
		}
	}

	public function getCollectionData(string $collectionFolderName, string $collectionRequestPath, bool $includeHidden = false) {
		if (parse_url($collectionRequestPath, PHP_URL_QUERY)) {
			throw new Exception('Suspicious collection path');
		}

		$collectionRootPath = $this->system->dirCollection . '/' . $collectionFolderName;

		if (str_contains($collectionRootPath, '..')) {
			throw new Exception('Suspicious collection path');
		}

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
		$filesJsonPath = $collectionAbsolutePath . '/.lipupini/files.json';
		$skipFiles = [];
		if (file_exists($filesJsonPath)) {
			$collectionData = json_decode(file_get_contents($filesJsonPath), true);
			// Process collection data first, since it can determine the display order
			foreach ($collectionData as $filename => $fileData) {
				if ($collectionRelativePath) {
					$filename = $collectionRelativePath . '/' . $filename;
				}
				if (in_array($fileData['visibility'] ?? null, ['hidden', 'unlisted'], true)) {
					$skipFiles[] = $filename;
					if (!$includeHidden) {
						continue;
					}
				}
				if (!file_exists($collectionRootPath . '/' . $filename)) {
					throw new Exception('Could not find file for entry in ' . $collectionFolderName . '/.lipupini/files.json');
				}
				$return[$filename] = $fileData;
			}
		}
		foreach (new \DirectoryIterator($collectionAbsolutePath) as $fileData) {
			if ($fileData->isDot() || $fileData->getFilename()[0] === '.') {
				continue;
			}
			$filePath = $collectionRelativePath ? $collectionRelativePath . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (!$includeHidden && in_array($filePath, $skipFiles, true)) {
				continue;
			}
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			// Get data from `$collectionData` if exists
			$return[$filePath] = array_key_exists($fileData->getFilename(), $collectionData) ? $collectionData[$fileData->getFilename()] : [];
		}
		return $return;
	}

	public function getCollectionDataRecursive(string $collectionFolderName) {
		$collectionData = $this->getCollectionData($collectionFolderName, '');
		$dirCollectionFolder = $this->system->dirCollection . '/' . $collectionFolderName;

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirCollectionFolder), \RecursiveIteratorIterator::SELF_FIRST) as $filePath => $item) {
			if ($item->getFilename()[0] === '.' || preg_match('#/\.#', $filePath) || !$item->isDir()) {
				continue;
			}
			$collectionRequestPath = preg_replace('#^' . preg_quote($dirCollectionFolder) . '/#', '', $filePath);
			$collectionData += $this->getCollectionData($collectionFolderName, $collectionRequestPath);
		}

		foreach ($collectionData as $fileName => $metaData) {
			// Excluding directories
			if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
				unset($collectionData[$fileName]);
			}
		}

		return $collectionData;
	}
}
