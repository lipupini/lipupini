<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class VideoPoster {
	public static function cacheSymlinkVideoPoster(State $systemState, string $collectionFolder, string $posterPath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolder);
		$fileCachePath = $cache->path() . '/video-poster/' . $posterPath;

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Symlinking video poster for `' . $posterPath . '`...' . "\n";
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		symlink(
			$systemState->dirCollection . '/' . $collectionFolder . '/.lipupini/video-poster/' . $posterPath,
			$fileCachePath
		);
	}
}
