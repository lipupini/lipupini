<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\Collection\Utility;
use Module\Lipupini\State;

class AudioThumbnail {
	public static function cacheSymlinkAudioThumbnail(State $systemState, string $collectionFolderName, string $audioPath, bool $echoStatus = false): false|string {
		$cache = new Cache($systemState, $collectionFolderName);
		$thumbnailPath = $audioPath . '.png';
		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionFolderName . '/.lipupini/thumbnail/' . $thumbnailPath;

		if (!file_exists($thumbnailPathFull)) {
			return false;
		}

		$fileCachePath = $cache->path() . '/thumbnail/' . $thumbnailPath;

		$cache::staticCacheSymlink($systemState, $collectionFolderName);

		// One tradeoff with doing this first is that the file can be deleted from the collection's `thumbnail` folder but still show if it stays in `cache`
		// The benefit is that it won't try to use `ffmpeg` and grab the frame if it hasn't yet, so it's potentially faster to check this way
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionFolderName . '/' . $audioPath)) {
			return false;
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		// If `$fileCachePath` is already there we don't need to do a cache symlink, and we can use what's there
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if ($echoStatus) {
			echo 'Symlinking audio thumbnail to cache for `' . $thumbnailPath . '`...' . "\n";
		}

		// Link the thumbnail path to the collection's cache
		$cache::createSymlink(
			$thumbnailPathFull,
			$fileCachePath
		);

		return $fileCachePath;
	}
}
