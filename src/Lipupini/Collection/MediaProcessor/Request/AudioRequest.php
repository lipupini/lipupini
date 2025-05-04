<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Audio;

class AudioRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		if (!preg_match('#^audio/(.+\.(' . implode('|', array_keys($this->system->mediaType['audio'])) . '))$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$filePath = $matches[1];
		$extension = $matches[2];

		// Once the file is symlinked, the file is considered cached and should be served statically on subsequent page refreshes
		$this->serve(
			Audio::cacheSymlink($this->system, $this->collectionName, 'audio', $filePath),
			$this->system->mediaType['audio'][$extension]
		);
	}
}
