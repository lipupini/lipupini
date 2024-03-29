<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Audio;

class AudioRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/audio/(.+\.(' . implode('|', array_keys($this->system->mediaType['audio'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
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
			Audio::cacheSymlink($this->system, $collectionFolderName, 'audio', $filePath),
			$this->system->mediaType['audio'][$extension]
		);
	}
}
