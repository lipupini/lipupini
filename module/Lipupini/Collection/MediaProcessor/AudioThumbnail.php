<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class AudioThumbnail {
	public static function cacheSymlinkAudioThumbnail(State $systemState, string $collectionName, string $audioPath, bool $echoStatus = false): false|string {
		$cache = new Cache($systemState, $collectionName);
		$thumbnailPath = $audioPath . '.png';
		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/audio/thumbnail/' . $thumbnailPath;

		if (!file_exists($thumbnailPathFull)) {
			return false;
		}

		$fileCachePath = $cache->path() . '/audio/thumbnail/' . $thumbnailPath;

		$cache::staticCacheSymlink($systemState, $collectionName);

		// One tradeoff with doing this first is that the file can be deleted from the collection's `thumbnail` folder but still show if it stays in `cache`
		// The benefit is that it won't try to use `ffmpeg` and grab the frame if it hasn't yet, so it's potentially faster to check this way
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionName . '/' . $audioPath)) {
			return false;
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
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
