<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;

class AudioWaveformRequest extends MediaProcessorRequest {
	use Collection\MediaProcessor\Trait\CacheSymlink;

	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		if (!preg_match('#^audio/waveform/(.+\.(' . implode('|', array_keys($this->system->mediaType['audio'])) . ')\.(' . implode('|', array_keys($this->system->mediaType['image'])) . '))$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$waveformPath = $matches[1];
		$waveformExtension = $matches[3];
		$audioPath = preg_replace('#\.' . $waveformExtension . '$#', '', $waveformPath);

		(new Collection\Utility($this->system))->validateCollectionName($this->collectionName);

		$this->serve(
			Collection\MediaProcessor\AudioWaveform::cacheSymlinkAudioWaveform($this->system, $this->collectionName, $audioPath),
			$this->system->mediaType['image'][$waveformExtension]
		);
	}
}
