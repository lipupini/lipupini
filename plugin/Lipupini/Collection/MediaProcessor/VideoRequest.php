<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini\Collection;

class VideoRequest extends MediaProcessorRequest {
	public function initialize(): void {
		$extMimes = [
			'mp4' => 'video/mp4',
		];

		if (!preg_match('#^/c/file/([^/]+)/video/(.+\.(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = $matches[2];
		$extension = $matches[3];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);
		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath;
		$this->cacheAndServe($pathOriginal, $extMimes[$extension]);
	}
}
