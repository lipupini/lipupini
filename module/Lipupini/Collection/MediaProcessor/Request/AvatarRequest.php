<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Avatar;

class AvatarRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		$avatarMimeTypes = [
			'png' => 'image/png',
		];

		if (!preg_match('#^avatar\.(' . implode('|', array_keys($avatarMimeTypes)) . ')$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$extension = $matches[1];

		(new Collection\Utility($this->system))->validateCollectionName($this->collectionName);

		$avatarPath = $this->system->dirCollection . '/' . $this->collectionName . '/.lipupini/avatar.png';

		$this->serve(
			Avatar::cacheSymlinkAvatar($this->system, $this->collectionName, $avatarPath),
			$avatarMimeTypes[$extension]
		);
	}
}
