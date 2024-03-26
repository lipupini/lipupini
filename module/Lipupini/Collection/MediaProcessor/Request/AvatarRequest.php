<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Avatar;

class AvatarRequest extends MediaProcessorRequest {
	public function initialize(): void {
		$avatarMimeTypes = [
			'png' => 'image/png',
		];

		if (!preg_match('#^/c/([^/]+)/avatar\.(' . implode('|', array_keys($avatarMimeTypes)) . ')$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$extension = $matches[2];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		$avatarPath = $this->system->dirCollection . '/' . $collectionFolderName . '/.lipupini/avatar.png';

		if (!file_exists($avatarPath)) {
			$avatarPath = $this->system->dirWebroot . '/img/avatar-default.png';
		}

		Avatar::cacheSymlinkAvatar($this->system, $collectionFolderName, $avatarPath);
		$this->serve($avatarPath, $avatarMimeTypes[$extension]);
	}
}
