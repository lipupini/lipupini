<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Avatar {
	public const DEFAULT_IMAGE_PATH = '/img/avatar-default.png';

	public static function cacheSymlinkAvatar(State $systemState, string $collectionName, string $avatarPath, bool $echoStatus = false): string {
		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/avatar.png';

		$cache::staticCacheSymlink($systemState, $collectionName);

		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if ($echoStatus) {
			echo 'Symlinking avatar for `' . $collectionName . '`...' . "\n";
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		// Use a default avatar if none is specified
		if (!file_exists($avatarPath)) {
			$avatarPath = $systemState->dirWebroot . self::DEFAULT_IMAGE_PATH;
		}

		$cache::createSymlink($avatarPath, $fileCachePath);
		return $fileCachePath;
	}

	public static function avatarUrlPath(State $systemState, string $collectionName) {
		return $systemState->staticMediaBaseUri . $collectionName . '/avatar.png';
	}
}
