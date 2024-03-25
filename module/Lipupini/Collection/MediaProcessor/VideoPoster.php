<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class VideoPoster {
	public static function cacheSymlinkVideoPoster(State $systemState, string $collectionFolderName, string $posterPath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolderName);
		$fileCachePath = $cache->path() . '/video-poster/' . $posterPath;

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Symlinking video poster to cache for `' . $posterPath . '`...' . "\n";
		}

		$cache::webrootCacheSymlink($systemState, $collectionFolderName, $echoStatus);

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		var_dump($systemState->dirCollection . '/' . $collectionFolderName . '/.lipupini/video-poster/' . $posterPath);

		symlink(
			$systemState->dirCollection . '/' . $collectionFolderName . '/.lipupini/video-poster/' . $posterPath,
			$fileCachePath
		);
	}
}
