<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection\MediaProcessor\Video;
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
		// Process the media file data specified in `files.json` if exists
		if (file_exists($filesJsonPath)) {
			// Grab the media file data from `files.json` into an array
			$collectionData = json_decode(file_get_contents($filesJsonPath), true);
			// Process collection data first, since it can determine the display order
			foreach ($collectionData as $filename => $fileData) {
				// Construct the relative path of the current media file
				if ($collectionRelativePath) {
					$filename = $collectionRelativePath . '/' . $filename;
				}
				// If the file is set to be hidden or unlisted, add it to the `$skipFiles` array
				if (in_array($fileData['visibility'] ?? null, ['hidden', 'unlisted'], true)) {
					$skipFiles[] = $filename;
					// Don't add file to return array if we are not including hidden files
					if (!$includeHidden) {
						continue;
					}
				}
				if (!file_exists($collectionRootPath . '/' . $filename)) {
					throw new Exception('Could not find file for entry in `' . $collectionFolderName . '/.lipupini/files.json`: ' . $filename);
				}
				// Add the file's data to the return array
				$return[$filename] = $fileData;
			}
		}
		// Here we pick up any files that are not explicitly added to `files.json`
		foreach (new \DirectoryIterator($collectionAbsolutePath) as $fileData) {
			// Skip dot files and any hidden files by checking if the first character is a dot
			if ($fileData->getFilename()[0] === '.') {
				continue;
			}
			// May be in a subdirectory relative to the collection root
			$filePath = $collectionRelativePath ? $collectionRelativePath . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (!$includeHidden && in_array($filePath, $skipFiles, true)) {
				continue;
			}
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			// Initialize media file's data to empty array since it doesn't have an entry in `files.json`
			$return[$filePath] = [];
		}

		$videoExtensions = array_keys($this->system->mediaType['video']);

		foreach ($return as $mediaFilePath => $mediaFileData) {
			// Loop through videos to process thumbnails
			if (in_array(pathinfo($mediaFilePath, PATHINFO_EXTENSION), $videoExtensions)) {
				// If the video has a thumbnail specified in `files.json` already then skip it
				if (!empty($mediaFileData['thumbnail'])) {
					continue;
				}
				// Check if a thumbnail is saved by the same name
				$thumbnailFile = $collectionAbsolutePath . '/.lipupini/thumbnail/' . $mediaFilePath . '.png';
				if (!file_exists($thumbnailFile)) {
					continue;
				}
				// We found a thumbnail file so add it to `$return`
				$return[$mediaFilePath]['thumbnail'] = $mediaFilePath . '.png';
			}
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

		// `getCollectionData` must return directories, but `getCollectionDataRecursive` cannot
		// Perhaps this could be revisited and handled differently
		foreach ($collectionData as $fileName => $metaData) {
			// Excluding directories
			if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
				unset($collectionData[$fileName]);
			}
		}

		return $collectionData;
	}

	public function allCollectionFolders(): array {
		$dir = new \DirectoryIterator($this->system->dirCollection);
		$collectionFolders = [];
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDir() || $fileinfo->getFilename()[0] === '.') {
				continue;
			}

			$collectionFolders[] = $fileinfo->getFilename();
		}
		return $collectionFolders;
	}
}
