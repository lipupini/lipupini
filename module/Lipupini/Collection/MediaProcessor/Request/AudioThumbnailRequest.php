<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;

class AudioThumbnailRequest extends MediaProcessorRequest {
	use Collection\MediaProcessor\Trait\CacheSymlink;

	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		if (!preg_match('#^audio/thumbnail/(.+\.(' . implode('|', array_keys($this->system->mediaType['audio'])) . ')\.(' . implode('|', array_keys($this->system->mediaType['image'])) . '))$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$thumbnailPath = $matches[1];
		$thumbnailExtension = $matches[3];
		$audioPath = preg_replace('#\.' . $thumbnailExtension . '$#', '', $thumbnailPath);

		(new Collection\Utility($this->system))->validateCollectionName($this->collectionName);

		$this->serve(
			Collection\MediaProcessor\AudioThumbnail::cacheSymlinkAudioThumbnail($this->system, $this->collectionName, $audioPath),
			$this->system->mediaType['image'][$thumbnailExtension]
		);
	}
}
