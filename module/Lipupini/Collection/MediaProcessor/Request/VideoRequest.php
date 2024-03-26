<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Video;

class VideoRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/video/(.+\.(' . implode('|', array_keys(Video::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = urldecode($matches[2]);
		$extension = $matches[3];

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);
		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath;

		// Once the file is symlinked, the file is considered cached and should be served statically on subsequent page refreshes
		Video::cacheSymlink($this->system, $collectionFolderName, 'video', $filePath);
		$this->serve($pathOriginal, Video::mimeTypes()[$extension]);
	}
}
