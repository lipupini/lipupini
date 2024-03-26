<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use Imagine;
use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Image;

class ImageRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/image/(' . implode('|', array_keys($this->system->mediaSizes)) . ')/(.+\.(' . implode('|', array_keys($this->system->mediaTypes['image'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$sizePreset = $matches[2];
		$imagePath = rawurldecode($matches[3]);
		$extension = $matches[4];

		// We can use the same function that `Module\Lipupini\Collection\Request` uses
		// Doing it again here because this one comes from a different part of a URL from the regex
		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $imagePath;

		Image::processAndCache($this->system, $collectionFolderName, 'image', $sizePreset, $imagePath);
		$this->serve($pathOriginal, $this->system->mediaTypes['image'][$extension]);
	}
}
