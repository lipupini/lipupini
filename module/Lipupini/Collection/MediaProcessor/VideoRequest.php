<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection;

class VideoRequest extends MediaProcessorRequest {
	public static function mimeTypes(): array {
		return [
			'mp4' => 'video/mp4',
		];
	}

	public function initialize(): void {
		if (!preg_match('#^/c/file/([^/]+)/video/(.+\.(' . implode('|', array_keys(self::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = $matches[2];
		$extension = $matches[3];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);
		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath;
		$this->cacheAndServe($pathOriginal, self::mimeTypes()[$extension]);
	}
}
