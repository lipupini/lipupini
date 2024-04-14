<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Text;

class TextRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^' . preg_quote(static::relativeStaticCachePath($this->system)) . '([^/]+)/text/(.+\.(' . implode('|', array_keys($this->system->mediaType['text'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionName = $matches[1];
		$filePath = rawurldecode($matches[2]);
		$extension = $matches[3];

		if ($extension === 'html') {
			$mdFilePath = rawurldecode(preg_replace('#\.html$#', '', $filePath));
		} else {
			$mdFilePath = rawurldecode($_SERVER['REQUEST_URI'] . '.html');
		}

		$pathOriginal = $this->system->dirCollection . '/' . $collectionName . '/' . $mdFilePath;

		(new Collection\Utility($this->system))->validateCollectionName($collectionName);

		if (!file_exists($pathOriginal)) {
			return;
		}

		$this->system->responseType = $this->system->mediaType['text'][$extension];
		$this->system->responseContent = file_get_contents(
			Text::processAndCache($this->system, $collectionName, 'text', $mdFilePath)
		);
	}
}
