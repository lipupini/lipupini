<?php

namespace Module\Lipupini\Collection\MediaProcessor\Trait;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

trait CacheSymlink {
	public static function cacheSymlink(State $systemState, string $collectionFolder, string $fileTypeFolder, string $filePath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolder);
		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath;

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Symlinking cache files for `' . $filePath . '`...' . "\n";
		} else {
			error_log('Symlinking cache files for `' . $filePath . '`...');
		}

		$collectionPath = $systemState->dirCollection . '/' . $collectionFolder;

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		symlink($collectionPath . '/' . $filePath, $fileCachePath);
	}
}
