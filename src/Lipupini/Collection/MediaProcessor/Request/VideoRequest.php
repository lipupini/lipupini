<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Video;

class VideoRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		if (!preg_match('#^video/(.+\.(' . implode('|', array_keys($this->system->mediaType['video'])) . '))$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$filePath = $matches[1];
		$extension = $matches[2];

		(new Collection\Utility($this->system))->validateCollectionName($this->collectionName);

		// Once the file is symlinked, the file is considered cached and should be served statically on subsequent page refreshes
		$this->serve(
			Video::cacheSymlink($this->system, $this->collectionName, 'video', $filePath),
			$this->system->mediaType['video'][$extension]
		);
	}
}
