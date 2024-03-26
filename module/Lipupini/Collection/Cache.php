<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;
use Module\Lipupini\Collection;

class Cache {
	private string $path;

	public function __construct(private State $system, protected string $collectionFolderName) {
		$path = $this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/cache';

		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}

		$this->path = $path;
	}

	public function path() {
		return $this->path;
	}

	public static function fileTypes() {
		return [
			'video' => MediaProcessor\Video::mimeTypes(),
			'audio' => MediaProcessor\Audio::mimeTypes(),
			'image' => MediaProcessor\Image::mimeTypes(),
			'text' => MediaProcessor\Text::mimeTypes(),
		];
	}

	public static function webrootCacheSymlink(State $systemState, string $collectionFolderName, bool $echoStatus = false) {
		$webrootCacheDir = $systemState->dirWebroot . '/c/' . $collectionFolderName;

		if ($echoStatus) {
			echo 'Creating `webroot` static cache symlink at `' . $webrootCacheDir . '`...' . "\n";
		}

		static::createSymlink((new Cache($systemState, $collectionFolderName))->path(), $webrootCacheDir);
	}

	// This handles a few extra useful steps with managing symlink creation
	public static function createSymlink(string $linkSource, string $linkTarget, bool $echoStatus = false) {
		if (file_exists($linkTarget)) {
			return;
		}

		// If it's a symlink but not `file_exists`, the symlink is broken so delete it first
		if (is_link($linkTarget)) {
			if ($echoStatus) {
				echo 'Deleting broken symlink at `' . $linkSource . '`...';
			}
			unlink($linkTarget);
		}

		if ($echoStatus) {
			echo 'Creating symlink from `' . $linkSource . ' to `' . $linkTarget . '`';
		}

		symlink($linkSource, $linkTarget);
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
			if (!in_array($fileType, array_keys(static::fileTypes()))) {
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
	public function cleanCacheDir(string $collectionPath, bool $echoStatus = false) {
		foreach ($this->prepareCacheData() as $fileType => $filePaths) {
			if ($fileType === 'image') {
				foreach ($filePaths as $imageSize => $imageFilePaths) {
					foreach ($imageFilePaths as $imageFilePath) {
						if (!file_exists($collectionPath . '/' . $imageFilePath)) {
							if ($echoStatus) {
								echo 'File does not exist in collection, deleting `' . $imageFilePath . '`...' . "\n";
							}
							unlink($this->path() . '/' . $fileType . '/' . $imageSize . '/' . $imageFilePath);
						}
					}
				}
				continue;
			}

			// Images above are a special case, process everything else here
			foreach ($filePaths as $filePath) {
				if ($fileType === 'text') {
					$filePath = preg_replace('#\.html$#', '', $filePath);
				}
				if (!file_exists($collectionPath . '/' . $filePath)) {
					if ($echoStatus) {
						echo 'File does not exist in collection, deleting `' . $filePath . '`...' . "\n";
					}
					unlink($this->path() . '/' . $fileType . '/' . $filePath);
				}
			}
		}
	}
}
