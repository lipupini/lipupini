<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;

class VideoThumbnailRequest extends MediaProcessorRequest {
	use Collection\MediaProcessor\Trait\CacheSymlink;

	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/thumbnail/(.+\.(' . implode('|', array_keys($this->system->mediaTypes['image'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$thumbnailPath = urldecode($matches[2]);
		$extension = $matches[3];
		$videoPath = preg_replace('#\.' . $extension . '$#', '', $thumbnailPath);

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		$this->serve(
			Collection\MediaProcessor\VideoThumbnail::cacheSymlinkVideoThumbnail($this->system, $collectionFolderName, $videoPath),
			$this->system->mediaTypes['image'][$extension]
		);
	}
}
