<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class AudioThumbnail {
	public static function cacheSymlinkAudioThumbnail(State $systemState, string $collectionName, string $audioPath, bool $echoStatus = false): false|string {
		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionName . '/' . $audioPath)) {
			return false;
		}

		$thumbnailPath = $audioPath . '.jpg';
		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/audio/thumbnail/' . $thumbnailPath;

		// If there's no custom cover art (thumbnail) present, there's nothing to do
		if (!file_exists($thumbnailPathFull)) {
			return false;
		}

		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/audio/thumbnail/' . $thumbnailPath;

		// If `$fileCachePath` is already there we don't need to do a cache symlink it so return
		if (file_exists($fileCachePath)) {
			if (is_link($fileCachePath)) {
				return $fileCachePath;
			}
			// If it's not a symlink, let's delete what's there and make it a symlink
			unlink($fileCachePath);
		} else {
			$fileCacheDir = pathinfo($fileCachePath, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDir)) {
				mkdir($fileCacheDir, 0755, true);
			}
		}

		if ($echoStatus) {
			echo 'Symlinking audio thumbnail to cache for `' . $thumbnailPath . '`...' . "\n";
		}

		// Link the thumbnail path to the collection's cache
		$cache::createSymlink(
			$thumbnailPathFull,
			$fileCachePath
		);

		// Create the collection's cache link in `webroot` if it does not exist
		$cache::staticCacheSymlink($systemState, $collectionName);

		return $fileCachePath;
	}
}
