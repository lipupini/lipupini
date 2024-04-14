<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;

class AudioWaveformRequest extends MediaProcessorRequest {
	use Collection\MediaProcessor\Trait\CacheSymlink;

	public function initialize(): void {
		if (!preg_match('#^' . preg_quote(static::relativeStaticCachePath($this->system)) . '([^/]+)/thumbnail/(.+\.(' . implode('|', array_keys($this->system->mediaType['audio'])) . ')\.waveform\.(' . implode('|', array_keys($this->system->mediaType['image'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionName = $matches[1];
		$thumbnailPath = rawurldecode($matches[2]);
		$thumbnailExtension = $matches[4];
		$audioPath = preg_replace('#\.waveform\.' . $thumbnailExtension . '$#', '', $thumbnailPath);

		(new Collection\Utility($this->system))->validateCollectionName($collectionName);

		$this->serve(
			Collection\MediaProcessor\AudioWaveform::cacheSymlinkAudioWaveform($this->system, $collectionName, $audioPath),
			$this->system->mediaType['image'][$thumbnailExtension]
		);
	}
}
