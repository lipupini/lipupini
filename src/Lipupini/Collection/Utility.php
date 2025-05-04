<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;

class Utility {
	public function __construct(private State $system) { }

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

	public function getCollectionData(string $collectionName, string $collectionFolder, bool $includeHidden = false) {
		if (parse_url($collectionFolder, PHP_URL_QUERY)) {
			throw new Exception('Suspicious collection path (E4)');
		}

		$collectionRootPath = $this->system->dirCollection . '/' . $collectionName;
		$collectionFolder = rtrim($collectionFolder, '/');

		if (pathinfo($collectionFolder, PATHINFO_EXTENSION)) {
			throw new Exception('`$collectionFolder` should be a directory, not a file');
		}

		$mediaTypesByExtension = $this->mediaTypesByExtension();

		$return = [];
		$filesJsonPath = $collectionRootPath . '/.lipupini/files.json';
		$skipFiles = [];
		// Process the media file data specified in `files.json` if exists
		if (file_exists($filesJsonPath)) {
			// Grab the media file data from `files.json` into an array
			$collectionFilesJsonData = json_decode(file_get_contents($filesJsonPath), true);
			// Process collection data first, since it can determine the display order
			foreach ($collectionFilesJsonData as $filePath => $fileData) {
				// If we are getting data from a collection subfolder, filter out other directories
				if ($collectionFolder) {
					if (!str_starts_with($filePath, $collectionFolder) || $filePath === $collectionFolder) {
						continue;
					}
				// If we are getting data from a collection root folder, filter out any subdirectories
				} else if (pathinfo($filePath, PATHINFO_DIRNAME) !== '.') {
					continue;
				}
				// If the file is set to be hidden or unlisted, add it to the `$skipFiles` array
				if (in_array($fileData['visibility'] ?? null, ['hidden', 'unlisted'], true)) {
					$skipFiles[] = $filePath;
					// Don't add file to return array if we are not including hidden files
					if (!$includeHidden) {
						continue;
					}
				}
				if (!file_exists($collectionRootPath . '/' . $filePath)) {
					throw new Exception('Could not find file for entry in `' . $collectionName . '/.lipupini/files.json`: ' . $filePath);
				}
				$extension = pathinfo($filePath, PATHINFO_EXTENSION);
				// Add the file's data to the return array
				$return[$filePath] = $fileData + ($extension ? $mediaTypesByExtension[$extension] : ['mediaType' => 'folder']);
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
			$filePath = $collectionFolder ? $collectionFolder . '/' . $fileData->getFilename() : $fileData->getFilename();
			if (!$includeHidden && in_array($filePath, $skipFiles, true)) {
				continue;
			}
			if (array_key_exists($filePath, $return)) {
				continue;
			}
			$extension = pathinfo($filePath, PATHINFO_EXTENSION);
			// Initialize media file's data with basic info since it doesn't have an entry in `files.json`
			$return[$filePath] = $extension ? $mediaTypesByExtension[$extension] : ['mediaType' => 'folder'];
		}

		// Process thumbnails
		$processThumbnailTypes = $this->mediaTypesByExtension();
		foreach ($return as $mediaFilePath => $mediaFileData) {
			// If it doesn't already have a caption, use the filename without the extension
			if (empty($mediaFileData['caption'])) {
				$return[$mediaFilePath]['caption'] = pathinfo($mediaFilePath, PATHINFO_FILENAME);
			}
			$extension = pathinfo($mediaFilePath, PATHINFO_EXTENSION);
			if ($extension === '') continue;
			$mediaType = explode('/', $processThumbnailTypes[$extension]['mediaType'])[0];
			// If the media file has a thumbnail specified in `files.json` already then skip it
			if (!empty($mediaFileData['thumbnail'])) {
				if (!parse_url($mediaFileData['thumbnail'], PHP_URL_HOST)) {
					// Reconstruct the thumbnail URL from `files.json` if it does not contain the hostname
					$return[$mediaFilePath]['thumbnail'] = $this->system->staticMediaBaseUri . $collectionName . '/' . $mediaType . '/thumbnail/' . $mediaFileData['thumbnail'];
				}
				continue;
			}
			// Check if a corresponding thumbnail file is saved by the same name
			$thumbnailFile = $collectionRootPath . '/.lipupini/' . $mediaType . '/thumbnail/' . $mediaFilePath . '.jpg';
			switch ($mediaType) {
				case 'audio':
					$waveformFile = $collectionRootPath . '/.lipupini/' . $mediaType . '/waveform/' . $mediaFilePath . '.png';
					// If the waveform file doesn't exist yet, we might be generating it with `ffmpeg`
					if (file_exists($waveformFile) || $this->system->useFfmpeg) {
						$return[$mediaFilePath]['waveform'] = $this->waveformUrl($collectionName, $mediaType . '/waveform', $mediaFilePath);
					}
					// The thumbnail file must exist to use it
					if (file_exists($thumbnailFile)) {
						$return[$mediaFilePath]['thumbnail'] = $this->thumbnailUrl($collectionName, $mediaType . '/thumbnail', $mediaFilePath);
					}
					break;

				case 'image':
					// Assume there is going to be a thumbnail for images
					$return[$mediaFilePath]['thumbnail'] = $this->assetUrl($collectionName, $mediaType . '/thumbnail', $mediaFilePath);
					break;

				case 'text':
					// The thumbnail file must exist to use it
					if (file_exists($thumbnailFile)) {
						$return[$mediaFilePath]['thumbnail'] = $this->thumbnailUrl($collectionName, $mediaType . '/thumbnail', $mediaFilePath);
					}
					break;

				case 'video':
					// If `useFfmpeg` is not enabled and the thumbnail does not already exist, then skip it because we won't try to create it in this case
					if (file_exists($thumbnailFile) || $this->system->useFfmpeg) {
						$return[$mediaFilePath]['thumbnail'] = $this->thumbnailUrl($collectionName, $mediaType . '/thumbnail', $mediaFilePath);
					}
					break;
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

	// https://beamtic.com/if-command-exists-php
	public function hasFfmpeg() {
		if (!$this->system->useFfmpeg) {
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

	public function assetUrl(string $collectionName, string $asset, string $collectionFilePath, bool $mustExist = false): string {
		$path = $asset . '/' . ltrim($collectionFilePath, '/');
		if ($mustExist && !file_exists((new Cache($this->system, $collectionName))->path() . '/' . $path)) {
			return '';
		}
		return $this->system->staticMediaBaseUri . $collectionName . '/' . $path;
	}

	public function thumbnailUrl(string $collectionName, string $asset, string $collectionFilePath, bool $mustExist = false): string {
		if (!str_starts_with($asset, 'image')) {
			$collectionFilePath .= '.jpg';
		}
		return $this->assetUrl($collectionName, $asset, $collectionFilePath, $mustExist);
	}

	public function waveformUrl(string $collectionName, string $asset, string $collectionFilePath, bool $mustExist = false): string {
		return $this->assetUrl($collectionName, $asset, $collectionFilePath . '.png', $mustExist);
	}

	// https://stackoverflow.com/q/7973790
	public static function urlEncodeUrl(string $url) {
		// If it's only a path and not a full URL, do it this way instead
		if (empty(parse_url($url, PHP_URL_HOST))) {
			return join('/', array_map('rawurlencode', explode('/', $url)));
		}

		// `parse_url` does not handle all the filepath characters that Lipupini wants to support
		// So we can't use `PHP_URL_PATH' key from that, instead we have to use RegExp
		return preg_replace_callback('#://([^/]+)/([^?]+)#', function ($match) {
			return '://' . $match[1] . '/' . self::urlEncodeUrl($match[2]);
		}, $url);
	}

	public function validateCollectionFolder(string $collectionName, string $collectionFolder): void {
		if (!is_dir($this->system->dirCollection . '/' . $collectionName . '/' . $collectionFolder)) {
			throw new Exception('Could not find collection folder: ' . htmlentities($collectionFolder), 404);
		}
	}

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
}
