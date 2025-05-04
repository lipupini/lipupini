<?php

namespace Module\Lipupini\Collection\MediaProcessor\Trait;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

trait CacheSymlink {
	public static function cacheSymlink(State $systemState, string $collectionName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): string {
		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		// Make sure the files exists in the collection before proceeding
		if (!file_exists($collectionPath . '/' . $filePath)) {
			return false;
		}

		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath;

		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		} else {
			$fileCacheDir = pathinfo($fileCachePath, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDir)) {
				mkdir($fileCacheDir, 0755, true);
			}
		}

		if ($echoStatus) {
			echo 'Symlinking cache files for `' . $filePath . '`...' . "\n";
		} else {
			error_log('Symlinking cache files for `' . $filePath . '`...');
		}

		$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePath);

		// Create the collection's cache link in `webroot` if it does not exist
		$cache::staticCacheSymlink($systemState, $collectionName);

		return $fileCachePath;
	}
}
