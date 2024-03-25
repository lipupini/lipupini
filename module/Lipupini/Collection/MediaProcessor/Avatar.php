<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Avatar {
	public static function mimeTypes(): array {
		return [
			'mp3' => 'audio/mp3',
			'm4a' => 'audio/m4a',
			'ogg' => 'audio/ogg',
			'flac' => 'audio/flac',
		];
	}

	public static function cacheSymlinkAvatar(State $systemState, string $collectionFolderName, string $avatarPath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolderName);
		$fileCachePath = $cache->path() . '/avatar.png';

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Symlinking avatar for `' . $collectionFolderName . '`...' . "\n";
		}

		$cache::webrootCacheSymlink($systemState, $collectionFolderName, $echoStatus);

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		symlink($avatarPath, $fileCachePath);
	}
}
