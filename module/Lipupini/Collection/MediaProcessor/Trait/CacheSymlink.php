<?php

namespace Module\Lipupini\Collection\MediaProcessor\Trait;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

trait CacheSymlink {
	public static function cacheSymlink(State $systemState, string $collectionFolderName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolderName);
		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath;

		$cache::webrootCacheSymlink($systemState, $collectionFolderName, $echoStatus);

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Symlinking cache files for `' . $filePath . '`...' . "\n";
		} else {
			error_log('Symlinking cache files for `' . $filePath . '`...');
		}

		$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePath);
	}
}
