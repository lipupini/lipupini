<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Video;

class VideoRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/video/(.+\.(' . implode('|', array_keys($this->system->mediaTypes['video'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = urldecode($matches[2]);
		$extension = $matches[3];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		// Once the file is symlinked, the file is considered cached and should be served statically on subsequent page refreshes
		$this->serve(
			Video::cacheSymlink($this->system, $collectionFolderName, 'video', $filePath),
			$this->system->mediaTypes['video'][$extension]
		);
	}
}
