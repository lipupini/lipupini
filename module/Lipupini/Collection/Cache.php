<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;
use Module\Lipupini\Collection\MediaProcessor\Request\MediaProcessorRequest;

class Cache {
	const DIRNAME = '.cache';
	private string $path;

	public function __construct(private State $system, protected string $collectionName, bool $private = false) {
		$path = $this->system->dirCollection . '/' . $this->collectionName . '/.lipupini/' . static::DIRNAME;
		if ($private) {
			$path .= '-private';
		}
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}

		$this->path = $path;
	}

	public function path() {
		return $this->path;
	}

	public static function staticCacheSymlink(State $systemState, string $collectionName) {
		static::createSymlink(
			(new Cache($systemState, $collectionName))->path(),
			$systemState->dirWebroot . MediaProcessorRequest::relativeStaticCachePath($systemState) . $collectionName
		);
	}

	// This handles a few extra useful steps with managing symlink cre	ation
	public static function createSymlink(string $linkTarget, string $linkName, bool $echoStatus = false) {
		if (!file_exists($linkTarget)) {
			throw new Exception('Could not find link target/source:' . $linkTarget);
		}

		clearstatcache(true, $linkName);

		// If link already exists and is valid
		if (file_exists($linkName)) {
			return;
		}

		// If it's a symlink but not `file_exists`, the symlink is broken so delete it first
		if (is_link($linkName)) {
			if ($echoStatus) {
				echo 'Deleting broken symlink at `' . $linkName . '`...';
			}
			unlink($linkName);
		}

		if ($echoStatus) {
			echo 'Creating symlink from `' . $linkTarget . ' to `' . $linkName . '`';
		}

		symlink($linkTarget, $linkName);
	}

	public function prepareCacheData() {
		$cacheDataPrepared = [];

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path())) as $filePath => $fileInfo) {
			if ($fileInfo->getFilename()[0] === '.') {
				continue;
			}

			// Skip collection avatar
			if ($fileInfo->getFilename() === 'avatar.png') {
				continue;
			}

			// File paths should start with the cache path followed by filetype (image, audio, video, text, etc)
			if (!preg_match('#^' . preg_quote($this->path()) . '/([^/]+)/#', $filePath, $matches)) {
				echo 'Unexpected cache file path: ' . $filePath . "\n";
				continue;
			}

			$fileType = $matches[1];

			// Only process known folder types
			if (!in_array($fileType, array_keys($this->system->mediaType))) {
				continue;
			}

			$filePathPrepared = preg_replace('#^' . preg_quote($matches[0]) . '#', '', $filePath);
			$cacheDataPrepared[$fileType][] = $filePathPrepared;
		}

		if (!empty($cacheDataPrepared['image'])) {
			$cacheDataPreparedImage = $cacheDataPrepared['image'];
			unset($cacheDataPrepared['image']);

			foreach ($cacheDataPreparedImage as $image) {
				if (!preg_match('#^([^/]+)/#', $image, $matches)) {
					echo 'Unexpected image size value: ' . $image . "\n";
					continue;
				}

				$imageSize = $matches[1];
				$cacheDataPrepared['image'][$imageSize][] = preg_replace('#^' . $imageSize . '/#', '', $image);
			}
		}

		return $cacheDataPrepared;
	}

	// Delete cache data that doesn't exist in collection
	public function cleanCacheDir(State $systemState, string $collectionName, bool $echoStatus = false) {
		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		foreach ($this->prepareCacheData() as $fileType => $filePaths) {
			if ($fileType === 'image') {
				foreach ($filePaths as $imageSize => $imageFilePaths) {
					foreach ($imageFilePaths as $imageFilePath) {
						if (!file_exists($collectionPath . '/' . $imageFilePath)) {
							$cacheFilePath = $this->path() . '/' . $fileType . '/' . $imageSize . '/' . $imageFilePath;
							if ($echoStatus) {
								echo 'Image file does not exist in collection, deleting cache file `' . $cacheFilePath . '`...' . "\n";
							}
							unlink($cacheFilePath);
						}
					}
				}
				continue;
			}

			// Images above are a special case, process everything else here
			foreach ($filePaths as $cacheFilePath) {
				$collectionFilePath = $cacheFilePath;
				if ($fileType === 'text') {
					$collectionFilePath = preg_replace('#^(html|markdown)/#', '', $collectionFilePath);
					$collectionFilePath = preg_replace('#\.html$#', '', $collectionFilePath);
				}
				// Extract collection filename from thumbnail or waveform path (if applicable)
				if (preg_match('#^(?:thumbnail|waveform)/(.+)\.(?:' . implode('|', array_keys($this->system->mediaType['image'])) . ')$#', $collectionFilePath, $matches)) {
					$collectionFilePath = $matches[1];
				}
				if (!file_exists($collectionPath . '/' . $collectionFilePath)) {
					$cacheFilePathFull = $this->path() . '/' . $fileType . '/' . $cacheFilePath;
					if ($echoStatus) {
						echo 'Media file does not exist in collection, deleting cache file `' . $cacheFilePathFull . '`...' . "\n";
					}
					unlink($cacheFilePathFull);
					if ($fileType === 'text') {
						unlink($cacheFilePathFull . '.html');
					}
				}
			}
		}
	}
}
