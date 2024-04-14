<?php

namespace Module\Lipupini\Collection\MediaProcessor\Trait;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

trait CacheSymlink {
	public static function cacheSymlink(State $systemState, string $collectionName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): string {
		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath;

		$cache::staticCacheSymlink($systemState, $collectionName);

		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if ($echoStatus) {
			echo 'Symlinking cache files for `' . $filePath . '`...' . "\n";
		} else {
			error_log('Symlinking cache files for `' . $filePath . '`...');
		}

		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePath);

		return $fileCachePath;
	}
}
