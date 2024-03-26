<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Image;
use Module\Lipupini\Collection\MediaProcessor\VideoPoster;

class VideoPosterRequest extends MediaProcessorRequest {
	use Collection\MediaProcessor\Trait\CacheSymlink;

	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/video-poster/(.+\.(' . implode('|', array_keys($this->system->mediaTypes['image'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$posterPath = urldecode($matches[2]);
		$extension = $matches[3];
		$videoPath = preg_replace('#\.' . $extension . '$#', '', $posterPath);

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);
		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/.lipupini/video-poster/' . $posterPath;

		VideoPoster::cacheSymlinkVideoPoster($this->system, $collectionFolderName, $videoPath);
		$this->serve($pathOriginal, $this->system->mediaTypes['image'][$extension]);
	}
}
