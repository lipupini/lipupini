<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;

class Utility {
	public function __construct(private State $system) { }

	public function validateCollectionName(string $collectionName): void {
		if (!$collectionName || strlen($collectionName) > 200) {
			throw new Exception('Suspicious collection identifier (E1)');
		}

		if (substr_count($collectionName, '@')) {
			throw new Exception('Suspicious collection identifier (E2)');
		}

		if (!is_dir($this->system->dirCollection . '/' . $collectionName)) {
			throw new Exception('Collection not found: ' . htmlentities($collectionName), 404);
		}
	}

	public function validateCollectionFolder(string $collectionName, string $collectionFolder): void {
		if (!is_dir($this->system->dirCollection . '/' . $collectionName . '/' . $collectionFolder)) {
			throw new Exception('Could not find collection folder: ' . htmlentities($collectionFolder), 404);
		}
	}

	public function getCollectionData(string $collectionName, string $collectionFolder, bool $includeHidden = false) {
		if (parse_url($collectionFolder, PHP_URL_QUERY)) {
			throw new Exception('Suspicious collection path (E4)');
		}

		$collectionRootPath = $this->system->dirCollection . '/' . $collectionName;
		$collectionFolder = rtrim($collectionFolder, '/');

		if (pathinfo($collectionFolder, PATHINFO_EXTENSION)) {
			throw new Exception('`$collectionFolder` should be a directory, not a file');
		}

		// Not sure if it is only the browser that will prevent this type of breach
		if (str_contains($collectionRootPath, '..')) {
			throw new Exception('Suspicious collection path (E5)');
		}

		$return = [];
		$filesJsonPath = $collectionRootPath . '/.lipupini/files.json';
		$skipFiles = [];
		// Process the media file data specified in `files.json` if exists
		if (file_exists($filesJsonPath)) {
			// Grab the media file data from `files.json` into an array
			$collectionFilesJsonData = json_decode(file_get_contents($filesJsonPath), true);
			// Process collection data first, since it can determine the display order
			foreach ($collectionFilesJsonData as $filename => $fileData) {
				// If we are getting data from a collection subfolder, filter out other directories
				if ($collectionFolder) {
					if (!str_starts_with($filename, $collectionFolder) || $filename === $collectionFolder) {
						continue;
					}
				// If we are getting data from a collection root folder, filter out any subdirectories
				} else if (pathinfo($filename, PATHINFO_DIRNAME) !== '.') {
					continue;
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
					throw new Exception('Could not find file for entry in `' . $collectionName . '/.lipupini/files.json`: ' . $filename);
				}
				// Add the file's data to the return array
				$return[$filename] = $fileData;
			}
		}

		$collectionPathFull = $collectionFolder ? $collectionRootPath . '/' . $collectionFolder : $collectionRootPath;

		// Here we pick up any files that are not explicitly added to `files.json`
		foreach (new \DirectoryIterator($collectionPathFull) as $fileData) {
			// Skip dot files and any hidden files by checking if the first character is a dot
			if ($fileData->getFilename()[0] === '.') {
				continue;
			}
			// May be in a subdirectory relative to the collection root
			$filePath = $collectionFolder ? rtrim($collectionFolder, '/') . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (!$includeHidden && in_array($filePath, $skipFiles, true)) {
				continue;
			}
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			// Initialize media file's data to empty array since it doesn't have an entry in `files.json`
			$return[$filePath] = [];
		}

		// Process thumbnails for audio and video
		$processThumbnailTypes = array_merge($this->system->mediaType['audio'] ?? [], $this->system->mediaType['video'] ?? []);
		foreach ($return as $mediaFilePath => $mediaFileData) {
			// If it doesn't already have a caption, use the filename without the extension
			if (empty($mediaFileData['caption'])) {
				$return[$mediaFilePath]['caption'] = pathinfo($mediaFilePath, PATHINFO_FILENAME);
			}
			$extension = pathinfo($mediaFilePath, PATHINFO_EXTENSION);
			if (in_array(pathinfo($mediaFilePath, PATHINFO_EXTENSION), array_keys($processThumbnailTypes))) {
				$mediaType = str_starts_with($processThumbnailTypes[$extension], 'audio') ? 'audio' : 'video';
				// If the media file has a thumbnail specified in `files.json` already then skip it
				if (!empty($mediaFileData['thumbnail'])) {
					if (!parse_url($mediaFileData['thumbnail'], PHP_URL_HOST)) {
						$return[$mediaFilePath]['thumbnail'] = $this->system->staticMediaBaseUri . $collectionName . '/' . $mediaType . '/thumbnail/' . $mediaFileData['thumbnail'];
					}
					continue;
				}
				// Check if a corresponding thumbnail file is saved by the same name
				$thumbnailFile = $collectionRootPath . '/.lipupini/' . $mediaType . '/thumbnail/' . $mediaFilePath . '.png';
				// If `useFfmpeg` is not enabled and the thumbnail does not already exist, then skip it because we won't try to create it in this case
				if (file_exists($thumbnailFile) || ($mediaType === 'video' && $this->system->useFfmpeg)) {
					$return[$mediaFilePath]['thumbnail'] = $this->system->staticMediaBaseUri . $collectionName . '/' . $mediaType . '/thumbnail/' . $mediaFilePath . '.png';
				}
				if ($mediaType === 'audio') {
					$waveformFile = $collectionRootPath . '/.lipupini/' . $mediaType . '/waveform/' . $mediaFilePath . '.png';
					if (file_exists($waveformFile) || $this->system->useFfmpeg) {
						$return[$mediaFilePath]['waveform'] = $this->system->staticMediaBaseUri . $collectionName . '/' . $mediaType . '/waveform/' . $mediaFilePath . '.png';
					}
				}
			}
		}

		return $return;
	}

	public function getCollectionDataRecursive(string $collectionName) {
		$collectionData = $this->getCollectionData($collectionName, '');
		$dirCollectionFolder = $this->system->dirCollection . '/' . $collectionName;

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirCollectionFolder), \RecursiveIteratorIterator::SELF_FIRST) as $filePath => $item) {
			if ($item->getFilename()[0] === '.' || preg_match('#/\.#', $filePath) || !$item->isDir()) {
				continue;
			}
			$collectionFolder = preg_replace('#^' . preg_quote($dirCollectionFolder) . '/#', '', $filePath);
			$collectionData += $this->getCollectionData($collectionName, $collectionFolder);
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
		foreach ($dir as $fileInfo) {
			if (!$fileInfo->isDir() || $fileInfo->getFilename()[0] === '.') {
				continue;
			}

			$collectionFolders[] = $fileInfo->getFilename();
		}
		return $collectionFolders;
	}

	// https://beamtic.com/if-command-exists-php
	public static function hasFfmpeg(State $systemState) {
		if (!$systemState->useFfmpeg) {
			return false;
		}

		$commandName = 'ffmpeg';
		$testMethod = (false === stripos(PHP_OS, 'win')) ? 'command -v' : 'where';
		return null !== shell_exec($testMethod . ' ' . $commandName);
	}

	public function mediaTypesByExtension() {
		$mediaTypesByExtension = [];
		foreach ($this->system->mediaType as $mediaType => $value) {
			foreach ($value as $extension => $mimeType) {
				$mediaTypesByExtension[$extension] = ['mediaType' => $mediaType, 'mimeType' => $mimeType];
			}
		}
		return $mediaTypesByExtension;
	}
}
