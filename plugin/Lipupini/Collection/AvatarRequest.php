<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Collection;

class AvatarRequest extends MediaProcessor\MediaProcessorRequest {
	public static function mimeTypes(): array {
		return [
			'png' => 'image/png',
		];
	}

	public function initialize(): void {
		if (!preg_match('#^/c/avatar/([^/]+)(\.(' . implode('|', array_keys(self::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$extension = $matches[3];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		$avatarPath = $this->system->dirCollection . '/' . $collectionFolderName . '/.lipupini/.avatar.png';
		$this->cacheAndServe($avatarPath, self::mimeTypes()[$extension]);
	}
}
